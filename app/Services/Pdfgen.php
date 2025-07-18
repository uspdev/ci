<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class Pdfgen
{

    private $template;
    private $data = [];
    private $headerImg;
    private $headerImgWidth;
    private $footer;
    private $footerImg;
    private $footerImgWidth;
    private $backgroundPdf;
    private $imgs = [];
    private $html;

    public function setTemplate($tpl)
    {
        $this->template = $tpl;
    }

    /*
        * @param array() $data É um array contendo objetos ou arrays associativos
        * se for objeto coloca direto no template
        * se for array associativo:
        * - o array tem de conter um objeto ou array de objetos
        * - o nome tem de começar com 'bloco_'
        * - o template tem de ter um bloco com mesmo nome do array associativo
        * - o bloco do template recebe um objeto com o nome do array sem o 'bloco_' inicial
        */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function setHeaderImg($img, $width)
    {
        $this->headerImg = $img;
        $this->headerImgWidth = $width;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function setFooterImg($img, $width)
    {
        $this->footerImg = $img;
        $this->footerImgWidth = $width;
    }

    public function setBackground($pdf)
    {
        $this->backgroundPdf = $pdf;
    }

    /**
     * Posiciona uma figura no documento pdf
     * @param string $img nome do arquivo
     * @param int $x coordenada x da imagem (em mm)
     * @param int $y coordenada y da imagem (em mm)
     * @param int $w largura da imagem (em mm)
     * A altura é calculada automaticamente
     * Se o papel for Landscape, x e y são invertidos
     * @return void
     */
    public function putImg($img, $x, $y, $w)
    {
        $this->imgs[] = ['img' => $img, 'x' => $x, 'y' => $y, 'w' => $w];
    }

    public function parse()
    {
        if($this->isPdfTemplate()){
            $this->html = null;
            return null;
        }
        $viewData = array_merge($this->data, [
            'headerImg' => $this->headerImg ?? null,
            'headerImgWidth' => $this->headerImgWidth ?? null,
            'footer' => $this->footer ?? null,
            'footerImg' => $this->footerImg ?? null,
            'footerImgWidth' => $this->footerImgWidth ?? null,
            'backgroundPdf' => $this->backgroundPdf ?? null,
        ]);

        $this->html = view($this->template, $viewData)->render();
        return $this->html;
    }

    // ao invés de gerar um pdf retorna uma string html
    // similar ao pdf.
    public function getHTML()
    {
        if (empty($this->html)) {
            $this->parse();
        }

        return $this->html; //dados
    }

    public function parseBasicHtml($html, $pdf, $maxWidth = null, $leftMargin = 20, $topMargin = 20, $rightMargin = 20)
    {
        $html = str_replace(["\r", "\n"], '', $html);

        $inList = false;
        $bold = false;
        $italic = false;

        $headerStyles = [
            'h1' => ['size' => 18, 'style' => 'B', 'space' => 6],
            'h2' => ['size' => 16, 'style' => 'B', 'space' => 6],
            'h3' => ['size' => 14, 'style' => 'B', 'space' => 5],
            'h4' => ['size' => 12, 'style' => 'B', 'space' => 4],
            'h5' => ['size' => 11, 'style' => 'B', 'space' => 3],
            'h6' => ['size' => 10, 'style' => '',  'space' => 2],
        ];

        $pageWidth = $pdf->GetPageWidth();
        $usableWidth = $maxWidth ?? ($pageWidth - $leftMargin - $rightMargin);

        $lineHeight = 7;

        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $tag = strtolower($part);

            if (preg_match('/^<h[1-6]>$/', $tag)) {
                $level = substr($tag, 2, 1);
                $styles = $headerStyles["h$level"];
                $pdf->Ln($styles['space']);
                $pdf->SetFont('', $styles['style'], $styles['size']);
                continue;
            }

            if (preg_match('/^<\/h[1-6]>$/', $tag)) {
                $pdf->Ln(4);
                $pdf->SetFont('', ($bold && $italic ? 'BI' : ($bold ? 'B' : ($italic ? 'I' : ''))));
                $pdf->SetFontSize(12);
                continue;
            }

            switch ($tag) {
                case '<ul>':
                    $inList = true;
                    break;

                case '</ul>':
                    $inList = false;
                    break;

                case '<li>':
                    if ($inList) {
                        $pdf->Ln(5);
                        $pdf->SetX($leftMargin + 4);
                        $pdf->Cell(5, $lineHeight, '•', 0, 0);
                    }
                    break;

                case '</li>':
                    $pdf->Ln(3);
                    break;

                case '<br>':
                case '<br/>':
                case '<br />':
                    $pdf->Ln(5);
                    break;

                case '<b>':
                case '<strong>':
                    $bold = true;
                    $pdf->SetFont('', $italic ? 'BI' : 'B');
                    break;

                case '</b>':
                case '</strong>':
                    $bold = false;
                    $pdf->SetFont('', $italic ? 'I' : '');
                    break;

                case '<i>':
                case '<em>':
                    $italic = true;
                    $pdf->SetFont('', $bold ? 'BI' : 'I');
                    break;

                case '</i>':
                case '</em>':
                    $italic = false;
                    $pdf->SetFont('', $bold ? 'B' : '');
                    break;

                default:
                    $text = trim(strip_tags($part));
                    if ($text !== '') {
                        $pdf->MultiCell($usableWidth, $lineHeight, $text, 0, 'L');
                    }
                    break;
            }
        }
    }


    public function pdfBuild($dest = 'I', $cfg = [], $fieldMap = null, $path)
    {
        if($this->isPdfTemplate()) {
            $pdf = new \setasign\Fpdi\Tfpdf\Fpdi();
            $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
            $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
            $pdf->AddFont('DejaVu','I','DejaVuSans-Oblique.ttf',true);
            $pdf->AddFont('DejaVu','BI','DejaVuSans-BoldOblique.ttf',true);
            $pdf->SetMargins(20, 20, 20);
            $pageCount = $pdf->setSourceFile($this->template);
            for ($pagenum = 1; $pagenum <= $pageCount; $pagenum++) {
                $tpl = $pdf->importPage($pagenum);
                $pdf->AddPage($cfg['orientation'], $cfg['paper']);
                $pdf->useTemplate($tpl);
                $pdf->tplId = $tpl;

                if (!empty($this->data['imgs'])) {
                    foreach ($this->data['imgs'] as $img) {
                        if (($img['page'] ?? 1) == $pagenum) {
                            $pdf->Image(public_path($img['img']), $img['x'], $img['y'], $img['w']);
                        }
                    }
                }

                foreach ($fieldMap as $campo => $info) {
                    if (!empty($this->data[$campo]) && ($info['page'] ?? 1) == $pagenum) {
                        $pdf->SetFont('DejaVu', '', $info['font']);
                        $pdf->SetXY($info['x'], $info['y']);
                        $valor = $this->data[$campo];

                        if ($valor != strip_tags($valor)) {
                            $this->parseBasicHtml($valor, $pdf, 184);
                        } else {
                            $pdf->Cell(0, 10, $valor, 0, 1, 'L');
                        }
                    }
                }
            }


            if (!empty($this->data['imgs'])) {
                foreach ($this->data['imgs'] as $img) {
                    $pdf->Image(public_path($img['img']), $img['x'], $img['y'], $img['w']);
                }
            }

            

            if ($dest === 'D') {
                return $pdf->Output('document.pdf', 'D');
            } elseif ($dest === 'F') {
                $pdf->Output($path, 'F');
                return $path;
            } else {
                return $pdf->Output('document.pdf', 'I');
            }
        } else {
            if (empty($this->html)) {
                $this->parse();
            }

            $pdf = Pdf::loadHTML($this->html);

            if (!empty($cfg['paper'])) {
                $pdf->setPaper($cfg['paper'], $cfg['orientation'] ?? 'portrait');
            }

            if ($dest === 'D') {
                return $pdf->download('document.pdf');
            } elseif ($dest === 'F') {
                $path = storage_path('app/document.pdf');
                $pdf->save($path);
                return $path;
            } else {
                return $pdf->stream('document.pdf');
            }
        }
    }


    function sexo($sexo, $m, $f)
    {
        if (strtolower($sexo) == 'm') {
            return $m;
        }

        if (strtolower($sexo) == 'f') {
            return $f;
        }

        return '';
    }

    function tipo($tipo, $m, $d) // mestrado ou doutorado
    {
        if (strtolower($tipo) == 'm') {
            return $m;
        }

        if (strtolower($tipo) == 'd') {
            return $d;
        }

        return '';
    }

    private function isPdfTemplate()
    {
        return strtolower(pathinfo($this->template, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getHash($fieldMap): string
    {
        $attributes = [
            'template' => $this->template,
            'fieldMap' => $fieldMap,
            'data' => $this->data,
            'headerImg' => $this->headerImg,
            'headerImgWidth' => $this->headerImgWidth,
            'footer' => $this->footer,
            'footerImg' => $this->footerImg,
            'footerImgWidth' => $this->footerImgWidth,
            'backgroundPdf' => $this->backgroundPdf,
            'imgs' => $this->imgs,
            'html' => $this->html,
        ];

        return md5(json_encode($attributes));
    }

}

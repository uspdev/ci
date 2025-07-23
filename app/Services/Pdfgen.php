<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;

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
        if($this->isDocxTemplate()){
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

    protected function parseHtmlToTextRun(string $html)
    {
        $textRun = new TextRun();

        $html = preg_replace('#<\s*br\s*/?>#i', "\n", $html);
        $html = preg_replace('#<\s*/?p\s*>#i', "\n", $html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        $this->parseNodeChildren($body, $textRun);

        return $textRun;
    }

    protected function parseNodeChildren(\DOMNode $node, TextRun $textRun, array $styleStack = [])
    {
        foreach ($node->childNodes as $child) {
            $currentStyle = $styleStack;

            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if ($tag === 'b' || $tag === 'strong') {
                    $currentStyle['bold'] = true;
                }
                if ($tag === 'i' || $tag === 'em') {
                    $currentStyle['italic'] = true;
                }
                if ($tag === 'u') {
                    $currentStyle['underline'] = 'single';
                }

                $this->parseNodeChildren($child, $textRun, $currentStyle);
            }

            if ($child instanceof \DOMText) {
                $content = trim($child->wholeText);
                if ($content !== '') {
                    $lines = explode("\n", $content);
                    foreach ($lines as $i => $line) {
                        if ($i > 0) {
                            $textRun->addTextBreak();
                        }
                        $textRun->addText($line, $currentStyle);
                    }
                }
            }
        }
    }

    public function docxBuild($pathToSave = null, $fieldMap = [])
    {

        if (!file_exists($this->template)) {
            throw new \Exception("Arquivo de template DOCX não encontrado em: $this->template");
        }

        $templateProcessor = new TemplateProcessor($this->template);

        foreach ($fieldMap as $campo => $info) {
            $valor = $this->data[$campo] ?? '';

            if ($valor == strip_tags($valor)) {
                $templateProcessor->setValue($campo, $valor);
            } else {
                $textRun = $this->parseHtmlToTextRun($valor);
                $templateProcessor->setComplexBlock($campo, $textRun);
            }

            $templateProcessor->setValue($campo, $valor);
        }
        $saveDocx = str_replace('.pdf', '.docx', $pathToSave);
        $templateProcessor->saveAs($saveDocx);

        $command = 'libreoffice --headless --convert-to pdf --outdir ' . escapeshellarg(dirname($saveDocx)) . ' ' . escapeshellarg($saveDocx);
        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($pathToSave)) {
            throw new \Exception("Falha ao converter o arquivo DOCX para PDF. Código: $resultCode");
        }

        if (file_exists($saveDocx)) {
            unlink($saveDocx);
        }

    }

    public function pdfBuild($dest = 'I', $cfg = [], $fieldMap = null, $path)
    {
        if($this->isDocxTemplate()) {
            $this->docxBuild($path, $fieldMap);
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

    private function isDocxTemplate()
    {
        return strtolower(pathinfo($this->template, PATHINFO_EXTENSION)) === 'docx';
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

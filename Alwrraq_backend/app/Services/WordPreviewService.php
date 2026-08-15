<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Throwable;
use ZipArchive;

class WordPreviewService
{
    private const WORD_NAMESPACE = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function toHtml(string $path): ?string
    {
        $documentXml = $this->documentXml($path);

        if (! $documentXml) {
            return null;
        }

        try {
            $document = new DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $loaded = $document->loadXML($documentXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        } catch (Throwable) {
            return null;
        }

        if (! $loaded) {
            return null;
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::WORD_NAMESPACE);
        $body = $xpath->query('//w:body')->item(0);

        if (! $body) {
            return null;
        }

        $html = '';
        foreach ($body->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $html .= match ($node->localName) {
                'p' => $this->paragraph($node, $xpath),
                'tbl' => $this->table($node, $xpath),
                default => '',
            };
        }

        return trim($html) !== '' ? $html : '<p>الملف لا يحتوي على نص قابل للعرض.</p>';
    }

    private function documentXml(string $path): ?string
    {
        if (class_exists(ZipArchive::class)) {
            try {
                $zip = new ZipArchive;
                if ($zip->open($path) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();

                    if (is_string($xml) && $xml !== '') {
                        return $xml;
                    }
                }
            } catch (Throwable) {
                // Continue with the dependency-free ZIP reader below.
            }
        }

        return $this->readZipEntry($path, 'word/document.xml');
    }

    /**
     * Read one ZIP entry without requiring the optional PHP zip extension.
     * DOCX files are ZIP containers, and some shared-hosting PHP builds do not
     * enable ZipArchive even though zlib is available.
     */
    private function readZipEntry(string $path, string $wantedEntry): ?string
    {
        try {
            $archive = file_get_contents($path);
            if (! is_string($archive) || strlen($archive) < 22) {
                return null;
            }

            $searchStart = max(0, strlen($archive) - 65_557);
            $endRecord = strrpos(substr($archive, $searchStart), "PK\x05\x06");
            if ($endRecord === false) {
                return null;
            }
            $endRecord += $searchStart;
            $centralOffset = unpack('Voffset', substr($archive, $endRecord + 16, 4))['offset'] ?? null;
            if (! is_int($centralOffset)) {
                return null;
            }

            $cursor = $centralOffset;
            $archiveLength = strlen($archive);
            while ($cursor + 46 <= $archiveLength && substr($archive, $cursor, 4) === "PK\x01\x02") {
                $header = unpack(
                    'vflags/vcompression/x4/x4/Vcompressed/Vuncompressed/vnameLength/vextraLength/vcommentLength/x8/VlocalOffset',
                    substr($archive, $cursor + 8, 38)
                );
                if (! is_array($header)) {
                    return null;
                }

                $name = substr($archive, $cursor + 46, $header['nameLength']);
                if ($name === $wantedEntry) {
                    if (($header['flags'] & 0x1) !== 0) {
                        return null;
                    }

                    $localOffset = $header['localOffset'];
                    if (substr($archive, $localOffset, 4) !== "PK\x03\x04") {
                        return null;
                    }
                    $localLengths = unpack(
                        'vnameLength/vextraLength',
                        substr($archive, $localOffset + 26, 4)
                    );
                    $dataOffset = $localOffset + 30 + $localLengths['nameLength'] + $localLengths['extraLength'];
                    $compressed = substr($archive, $dataOffset, $header['compressed']);

                    return match ($header['compression']) {
                        0 => $compressed,
                        8 => ($inflated = @gzinflate($compressed)) !== false ? $inflated : null,
                        default => null,
                    };
                }

                $cursor += 46 + $header['nameLength'] + $header['extraLength'] + $header['commentLength'];
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function paragraph(DOMElement $paragraph, DOMXPath $xpath): string
    {
        $content = '';
        foreach ($xpath->query('.//w:r', $paragraph) as $run) {
            $content .= $this->run($run, $xpath);
        }

        $alignment = $xpath->query('./w:pPr/w:jc', $paragraph)->item(0);
        $alignmentValue = $alignment instanceof DOMElement
            ? $alignment->getAttributeNS(self::WORD_NAMESPACE, 'val')
            : '';
        $textAlign = match ($alignmentValue) {
            'center' => 'center',
            'left' => 'left',
            'right' => 'right',
            'both', 'distribute' => 'justify',
            default => 'start',
        };

        return '<p dir="auto" style="text-align:'.$textAlign.'">'.($content !== '' ? $content : '<br>').'</p>';
    }

    private function run(DOMNode $run, DOMXPath $xpath): string
    {
        $content = '';
        foreach ($run->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->localName === 'rPr') {
                continue;
            }

            $content .= match ($child->localName) {
                't', 'instrText' => htmlspecialchars($child->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                'tab' => '&emsp;',
                'br', 'cr' => '<br>',
                default => '',
            };
        }

        if ($content === '') {
            return '';
        }

        if ($xpath->query('./w:rPr/w:u', $run)->length > 0) {
            $content = '<u>'.$content.'</u>';
        }
        if ($xpath->query('./w:rPr/w:i', $run)->length > 0) {
            $content = '<em>'.$content.'</em>';
        }
        if ($xpath->query('./w:rPr/w:b', $run)->length > 0) {
            $content = '<strong>'.$content.'</strong>';
        }

        return $content;
    }

    private function table(DOMElement $table, DOMXPath $xpath): string
    {
        $html = '<div class="word-table-wrap"><table class="word-table"><tbody>';

        foreach ($xpath->query('./w:tr', $table) as $row) {
            $html .= '<tr>';
            foreach ($xpath->query('./w:tc', $row) as $cell) {
                $cellHtml = '';
                foreach ($xpath->query('./w:p', $cell) as $paragraph) {
                    $cellHtml .= $this->paragraph($paragraph, $xpath);
                }
                $html .= '<td>'.($cellHtml !== '' ? $cellHtml : '&nbsp;').'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</tbody></table></div>';
    }
}

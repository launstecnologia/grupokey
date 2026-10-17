<?php

namespace App\Services;

class LgpdTermDocument
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const XML_NS = 'http://www.w3.org/XML/1998/namespace';

    public function generate(string $establishmentName, string $documentLabel, string $documentNumber, string $date): string
    {
        $template = dirname(__DIR__, 2) . '/storage/templates/termo-lgpd.docx';
        if (!is_file($template)) {
            throw new \RuntimeException('Modelo do termo LGPD não encontrado.');
        }

        $directory = dirname(__DIR__, 2) . '/storage/tmp';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Não foi possível preparar o arquivo do termo LGPD.');
        }

        $target = $directory . '/termo-lgpd-' . bin2hex(random_bytes(8)) . '.docx';
        if (!copy($template, $target)) {
            throw new \RuntimeException('Não foi possível copiar o modelo do termo LGPD.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($target) !== true) {
            @unlink($target);
            throw new \RuntimeException('Não foi possível abrir o termo LGPD.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            @unlink($target);
            throw new \RuntimeException('O modelo do termo LGPD está incompleto.');
        }

        $filled = $this->fillDocument($xml, $establishmentName, $documentLabel, $documentNumber, $date);
        $zip->addFromString('word/document.xml', $filled);
        $zip->close();

        return $target;
    }

    private function fillDocument(string $xml, string $establishmentName, string $documentLabel, string $documentNumber, string $date): string
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        if (!$dom->loadXML($xml)) {
            throw new \RuntimeException('Não foi possível ler o termo LGPD.');
        }

        $nameFilled = false;
        $dateFilled = false;

        foreach ($dom->getElementsByTagNameNS(self::WORD_NS, 'p') as $paragraph) {
            $text = trim($this->paragraphText($paragraph));

            if (!$nameFilled && strpos($text, 'CONTRATANTE:') === 0 && strpos($text, 'CPF') !== false) {
                $this->fillContractorParagraph($paragraph, $establishmentName, $documentLabel, $documentNumber);
                $nameFilled = true;
                continue;
            }

            if (!$dateFilled && preg_match('/^Data:\s*\/\//', $text)) {
                $this->fillDateParagraph($paragraph, $date);
                $dateFilled = true;
            }
        }

        if (!$nameFilled) {
            throw new \RuntimeException('Não foi possível localizar o nome da contratante no termo LGPD.');
        }

        return $dom->saveXML();
    }

    private function fillContractorParagraph(\DOMElement $paragraph, string $establishmentName, string $documentLabel, string $documentNumber): void
    {
        $nameInserted = false;
        $labelUpdated = false;

        foreach ($this->runs($paragraph) as $run) {
            $runText = $this->runText($run);

            if (!$nameInserted && strpos($runText, 'CONTRATANTE:') !== false) {
                continue;
            }

            if (!$nameInserted && $this->runHasTab($run)) {
                $this->insertRunText($run, $establishmentName . '  ');
                $nameInserted = true;
                continue;
            }

            if (!$labelUpdated && trim($runText) === 'CPF:') {
                $this->replaceRunText($run, $documentLabel . ':');
                $labelUpdated = true;
                continue;
            }

            if ($labelUpdated && $documentNumber !== '' && $this->runHasTab($run)) {
                $this->insertRunText($run, $documentNumber);
                break;
            }
        }

        if (!$nameInserted) {
            throw new \RuntimeException('Não foi possível preencher o nome do estabelecimento no termo LGPD.');
        }
    }

    private function fillDateParagraph(\DOMElement $paragraph, string $date): void
    {
        $first = true;
        $tabs = [];

        foreach ($paragraph->getElementsByTagNameNS(self::WORD_NS, 't') as $textNode) {
            if ($first) {
                $textNode->textContent = 'Data: ' . $date;
                $first = false;
                continue;
            }
            $textNode->textContent = '';
        }

        foreach ($paragraph->getElementsByTagNameNS(self::WORD_NS, 'tab') as $tab) {
            $tabs[] = $tab;
        }
        foreach ($tabs as $tab) {
            if ($tab->parentNode) {
                $tab->parentNode->removeChild($tab);
            }
        }
    }

    private function paragraphText(\DOMElement $paragraph): string
    {
        $text = '';
        foreach ($paragraph->getElementsByTagNameNS(self::WORD_NS, 't') as $textNode) {
            $text .= $textNode->textContent;
        }
        return $text;
    }

    private function runs(\DOMElement $paragraph): array
    {
        $runs = [];
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'r') {
                $runs[] = $child;
            }
        }
        return $runs;
    }

    private function runText(\DOMElement $run): string
    {
        $text = '';
        foreach ($run->getElementsByTagNameNS(self::WORD_NS, 't') as $textNode) {
            $text .= $textNode->textContent;
        }
        return $text;
    }

    private function runHasTab(\DOMElement $run): bool
    {
        return $run->getElementsByTagNameNS(self::WORD_NS, 'tab')->length > 0;
    }

    private function insertRunText(\DOMElement $run, string $value): void
    {
        $document = $run->ownerDocument;
        $textNode = $document->createElementNS(self::WORD_NS, 'w:t');
        $textNode->setAttributeNS(self::XML_NS, 'xml:space', 'preserve');
        $textNode->appendChild($document->createTextNode($value));

        $tab = $run->getElementsByTagNameNS(self::WORD_NS, 'tab')->item(0);
        if ($tab) {
            $run->insertBefore($textNode, $tab);
            return;
        }
        $run->appendChild($textNode);
    }

    private function replaceRunText(\DOMElement $run, string $value): void
    {
        $nodes = [];
        foreach ($run->getElementsByTagNameNS(self::WORD_NS, 't') as $textNode) {
            $nodes[] = $textNode;
        }
        if (!$nodes) {
            $this->insertRunText($run, $value);
            return;
        }
        $nodes[0]->textContent = $value;
        for ($i = 1; $i < count($nodes); $i++) {
            $nodes[$i]->textContent = '';
        }
    }
}

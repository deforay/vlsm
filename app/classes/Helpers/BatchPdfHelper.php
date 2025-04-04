<?php

namespace App\Helpers;

use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use setasign\Fpdi\Tcpdf\Fpdi;

class BatchPdfHelper extends Fpdi
{
    public ?string $logo;
    public ?string $text;
    public ?string $batch;
    public ?string $resulted;
    public ?string $reviewed;
    public ?string $createdBy;
    public ?string $worksheetName;


    public function setHeading($logo, $text, $batch, $resulted, $reviewed, $createdBy, $worksheetName): void
    {
        $this->logo = $logo;
        $this->text = $text;
        $this->batch = $batch;
        $this->resulted = $resulted;
        $this->reviewed = $reviewed;
        $this->createdBy = $createdBy;
        $this->worksheetName = $worksheetName;
    }
    //Page header
    public function Header(): void
    {

        if (trim($this->logo) != "") {
            if (MiscUtility::isImageValid(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
                $imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
                $this->Image($imageFilePath, 15, 10, 15, '', '', '', 'T');
            }
        }
        $this->SetFont('helvetica', '', 7);
        $this->writeHTMLCell(30, 0, 10, 26, $this->text, 0, 0, 0, true, 'A');
        $this->SetFont('helvetica', '', 13);
        $this->writeHTMLCell(0, 0, 0, 10, _translate('Batch Number/Code') . ' : ' . $this->batch, 0, 0, 0, true, 'C');
        $this->writeHTMLCell(0, 0, 0, 20, $this->worksheetName, 0, 0, 0, true, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->writeHTMLCell(0, 0, 144, 10, _translate('Result On') . ' : ' . $this->resulted, 0, 0, 0, true, 'C');
        $this->writeHTMLCell(0, 0, 144, 16, _translate('Reviewed On') . ' : ' . $this->reviewed, 0, 0, 0, true, 'C');
        $this->writeHTMLCell(0, 0, 144, 22, _translate('Created By') . ' : ' . $this->createdBy, 0, 0, 0, true, 'C');
        $html = '<hr />';
        $this->writeHTMLCell(0, 0, 10, 32, $html, 0, 0, 0, true, 'J');
    }

    // Page footer
    public function Footer(): void
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $text = _translate("Batch file generated on") . ' : ' . DateUtility::humanReadableDateFormat(DateUtility::getCurrentDateTime(), true);
        $this->Cell(0, 10, $text, 0, false, 'L  ', 0);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
    }
}

<?php

declare(strict_types=1);


namespace Sonata\Exporter\Writer;

use XLSXWriter;
use Zeiterfassung\Entity\TimeEntry;
use ZipArchive;

final class XlsxExporter extends XLSXWriter implements TypedWriterInterface
{
    // private string $filename;
    // #[Override]
    // public function __construct(string $filename)
    // {
    //     $this->filename = $filename;
    //     return parent::__construct();
    // }
    
    public function open(): void
    {
    }
    
    /**
     * @throws WriterException
    */
    public function close(): void
    {
        parent::writeToFile($this->tempFilename());
    }
    
    function getDefaultMimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function getFormat(): string
    {
        return 'xlsx';
    }

    public function write(array $data): void
    {
        parent::writeSheet($data);
    }

    

    public function writeAsTimeEntryReport(array $rawData): string
    {
        
        $data = $this->formatReportData($rawData);
        $zipName = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(_('Cannot open ' . $zipName));
        }

        // write into zip archive structured costumer/month.xlsx
        foreach ($data as $costumer => $months) {
            $zip->addEmptyDir($costumer);
            foreach ($months as $month => $entries) {
                
                $tmpName = tempnam(sys_get_temp_dir(), 'xlsx_');
                if(file_exists($tmpName)) unlink($tmpName);             // hacky way to just get a random name, not the new file
                $writer = new XlsxExporter();
                $writer->open();
                $writer->writeSheetRow('Sheet1', [$costumer]);
                $writer->writeSheetRow('Sheet1', []);
                $writer->writeSheetHeader('Sheet1', ['Datum'=>'string', 'Eintrag'=>'string', 'Austrag' => 'string'] );
                foreach($entries as $entry)     
                    $writer->writeSheetRow('Sheet1', $entry );
                $writer->writeToFile($tmpName);
                // $writer->close();
                // Handler::create($source, $writer)->export();
                // foreach ($entries as $key => $value) {
                //     $writer->add;
                // }

                $zip->addFile($tmpName, $costumer.DIRECTORY_SEPARATOR.$costumer.'_'.$month.'.xlsx');
            }
        }
        if (!$zip->close()) throw new \RuntimeException(_('Cannot close ' . $zipName));

        return $zipName;
    }
}
<?php

declare(strict_types=1);


namespace Sonata\Exporter\Writer;

use XLSXWriter;

final class XlsxExporter extends XLSXWriter implements TypedWriterInterface
{    
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

    

    public function writeAsTimeEntryReport(array $data, string $costumer): string
    {
        
        $tmpName = tempnam(sys_get_temp_dir(), 'xlsx_');
        if(file_exists($tmpName)) unlink($tmpName);             // hacky way to just get a random name, not the new file
        
        $this->open();
        $this->writeSheetRow('Sheet1', [$costumer]);
        $this->writeSheetRow('Sheet1', []);
        $this->writeSheetHeader('Sheet1', ['Datum'=>'string', 'Eintrag'=>'string', 'Austrag' => 'string'] );
        foreach($data as $entry)     
            $this->writeSheetRow('Sheet1', $entry );
        $this->writeToFile($tmpName);

        return $tmpName;
    }
}
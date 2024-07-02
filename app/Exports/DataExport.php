<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DataExport implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;
    protected $title;

    public function __construct(array $data, string $title)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Cover', 'Title', 'Artists', 'Plays', 'Downloads', 'Likes', 'Created At'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}

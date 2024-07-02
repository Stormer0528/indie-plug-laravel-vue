<?php

namespace App\Exports;

use App\Song;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class SongExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $start_date;
    protected $end_date;
    protected $search;

    public function __construct($start_date = null, $end_date = null, $search = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->search = $search;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Song::where('id', '<>', null)->with(['artist','likes', 'plays']);

        /**
         * Set Date Filter Logic
         */
        if($this->start_date){
            $query->whereDate('created_at', '>=', $this->start_date);
        }
        if($this->end_date){
            $query->whereDate('created_at', '<=', $this->end_date);
        }

        if($this->search){
            $query->where('title', 'LIKE', '%'.$this->search.'%');
        }

        $result = $query->orderBy('created_at', 'desc')->get();

        return $result;
    }

    public function map($song): array
    {
        return [
            $song->title,
            isset($song->artist->firstname) ? $song->artist->firstname.' '.$song->artist->lastname : null,
            $song->plays->count(),
            $song->download_count,
            $song->likes->count(),
            Carbon::parse($song->created_at)->format('M d Y')
        ];
    }

    public function headings(): array
    {
        return [
            'Title', 'Artists', 'Plays', 'Downloads', 'Likes', 'Created At'
        ];
    }
}

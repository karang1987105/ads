<?php


namespace App\Helpers;


use Illuminate\Database\Query\Builder;

class QBuilderHelper extends Builder {
    public bool $withFoundRow = false;
    public int $totalRows = 0;

    public function getCountForPagination($columns = ['*']) {
        if ($this->withFoundRow) {
            return $this->totalRows;
        }
        return parent::getCountForPagination($columns);
    }

}

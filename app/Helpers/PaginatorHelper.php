<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorHelper extends LengthAwarePaginator {
    public function render($view = null, $data = []) {
        return $this->pages($this->currentPage, ceil($this->total / $this->perPage));
    }

    private function getNumberHtml($number, $active = false, $clickable = true) {
        return "<li" . ($active ? ' class="active"' : (!$clickable ? ' class="disabled"' : '')) . ">"
            . "<span onclick=\"Ads.list.page(this, $number)\">$number</span>"
            . "</li>";
    }

    private function pages($currentPage, $totalPages) {

        if ($totalPages <= 1) {
            return '';
        }

        $pages = [];
        if ($totalPages > 9) {
            $startingGap = $currentPage >= 6;
            $endingGap = ($totalPages - $currentPage) > 3;
        } else {
            $startingGap = false;
            $endingGap = false;
        }

        if ($startingGap) {
            $pages[] = $this->getNumberHtml(1);
            $pages[] = $this->getNumberHtml('...', clickable: false);
            if ($endingGap) {
                $pages[] = $this->getNumberHtml($currentPage - 2);
                $pages[] = $this->getNumberHtml($currentPage - 1);
                $pages[] = $this->getNumberHtml($currentPage, true);
                $pages[] = $this->getNumberHtml($currentPage + 1);
                $pages[] = $this->getNumberHtml($currentPage + 2);
                $pages[] = $this->getNumberHtml('...', clickable: false);
                $pages[] = $this->getNumberHtml($totalPages);
            } else {
                for ($i = 6; $i >= 0; $i -= 1) {
                    $pages[] = $this->getNumberHtml($totalPages - $i, active: $totalPages - $i == $currentPage);
                }
            }
        } else {
            if ($endingGap) {
                for ($i = 1; $i <= 5; $i += 1) {
                    $pages[] = $this->getNumberHtml($i, active: $i == $currentPage);
                }
                $pages[] = $this->getNumberHtml(6);
                $pages[] = $this->getNumberHtml(7);
                $pages[] = $this->getNumberHtml('...', clickable: false);
                $pages[] = $this->getNumberHtml($totalPages);
            } else {
                for ($i = 1; $i <= $totalPages; $i += 1) {
                    $pages[] = $this->getNumberHtml($i, active: $i == $currentPage);
                }
            }
        }
        return join('', $pages);
    }
}

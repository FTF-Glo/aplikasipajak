<?php

function pagination($totalPages, $page, $stages = 3)
{
    // Initial page num setup
    if ($page == 0) {
        $page = 1;
    }
    $prev = $page - 1;
    $next = $page + 1;
    $lastpage = $totalPages;
    $lastpagem1 = $lastpage - 1;


    $paginate = '';
    if ($lastpage > 1) {
        $paginate .= "<nav aria-label=\"Pagination\"><ul class=\"pagination\">";
        // Previous
        if ($page > 1) {
            $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($prev) . "\">&laquo;</a></li>";
        } else {
            $paginate .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\">&laquo;</a></li>";
        }



        // Pages	
        if ($lastpage < 7 + ($stages * 2))    // Not enough pages to breaking it up
        {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $paginate .= "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\">$counter</a></li>";
                } else {
                    $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($counter) . "\">$counter</a></li>";
                }
            }
        } elseif ($lastpage > 5 + ($stages * 2))    // Enough pages to hide a few?
        {
            // Beginning only hide later pages
            if ($page < 1 + ($stages * 2)) {
                for ($counter = 1; $counter < 4 + ($stages * 2); $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\">$counter</a></li>";
                    } else {
                        $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($counter) . "\">$counter</a></li>";
                    }
                }
                $paginate .= "<li class=\"page-item ellipsis\"></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($lastpagem1) . "\">$lastpagem1</a></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($lastpage) . "\">$lastpage</a></li>";
            }
            // Middle hide some front and some back
            elseif ($lastpage - ($stages * 2) > $page && $page > ($stages * 2)) {
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl(1) . "\">1</a></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl(2) . "\">2</a></li>";
                $paginate .= "<li class=\"page-item ellipsis\"></li>";
                for ($counter = $page - $stages; $counter <= $page + $stages; $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\">$counter</a></li>";
                    } else {
                        $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($counter) . "\">$counter</a></li>";
                    }
                }
                $paginate .= "<li class=\"page-item ellipsis\"></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($lastpagem1) . "\">$lastpagem1</a></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($lastpage) . "\">$lastpage</a></li>";
            }
            // End only hide early pages
            else {
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl(1) . "\">1</a></li>";
                $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl(2) . "\">2</a></li>";
                $paginate .= "<li class=\"page-item ellipsis\"></li>";
                for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\">$counter</a></li>";
                    } else {
                        $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($counter) . "\">$counter</a></li>";
                    }
                }
            }
        }

        // Next
        if ($page < $counter - 1) {
            $paginate .= "<li class=\"page-item\"><a class=\"page-link\" href=\"" . makePaginationUrl($next) . "\">&raquo;</a></li>";
        } else {
            $paginate .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\">&raquo;</a></li>";
        }

        $paginate .= "</ul></nav>";

        return $paginate;
    }
}

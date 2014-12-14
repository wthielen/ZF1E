<?php

class ZFE_View_Helper_Paginator extends Zend_View_Helper_Abstract
{
    /**
     * The Paginator view helper
     *
     * Based on the pageInfo variable in the view, this view helper will create
     * the basic HTML structure for page links, together with the previous, next,
     * first page and last page links.
     *
     * This helper will use the pagination CSS classes as used by the Twitter
     * Bootstrap framework. Support for other frameworks may be added. Configurable
     * class names may be implemented.
     *
     * TODO Based on a function argument, this view helper will create a list of
     * page numbers, limit to show only a range around the current page number,
     * or show an input field that shows the current page number, but allows the
     * user to enter any page number.
     */
    public function paginator($pageInfo, $options = array())
    {
        $maxEntries = 11;
        $baseUrl = $this->view->url();

        // Some handy booleans
        $firstPage = $pageInfo['page'] == 1;
        $lastPage = $pageInfo['page'] == $pageInfo['pages'];

        $halfPoint = floor($maxEntries / 2);
        $start = $pageInfo['page'] > $halfPoint ? $pageInfo['page'] - $halfPoint : 1;
        $end = $start + $maxEntries - 1;

        if ($end > $pageInfo['pages']) $end = $pageInfo['pages'];
        if ($end - $start < $maxEntries) $start = max(1, $end - $maxEntries + 1);

        $html = '<ul class="pagination"';
        foreach($options as $key => $val) $html .= " data-$key=\"$val\"";
        $html .= '>';

        // If not on the first page, show the "go to first page" and the "go to
        // previous page" links.
        if (!$firstPage) {
            $html .= '<li class="first">';
            $url = $baseUrl . '?' . http_build_query(array('p' => 1));
            $html .= '<a href="' . $url . '" data-page="1">&lt;&lt;</a>';
            $html .= '</li>';

            $html .= '<li class="previous">';
            $url = $baseUrl . '?' . http_build_query(array('p' => $pageInfo['page'] - 1));
            $html .= '<a href="' . $url . '" data-page="' . ($pageInfo['page'] - 1) . '">&lt;</a>';
            $html .= '</li>';

            if ($start > 1) $html .= '<li class="disabled"><span>...</span></li>';
        }

        // Add the page number links, and make the current one active
        for($page = $start; $page <= $end; $page++) {
            $url = $baseUrl . '?' . http_build_query(array('p' => $page));

            $cls = array();
            if ($page == $pageInfo['page']) $cls[] = 'active';

            $html .= '<li class="' . implode(' ', $cls) . '">';
            $html .= '<a href="' . $url . '" data-page="' . $page . '">' . $page . '</a>';
            $html .= '</li>';
        }

        // If not on the last page, show the "go to next page" and "go to last
        // page" links.
        if (!$lastPage) {
            if ($end < $pageInfo['pages']) $html .= '<li class="disabled"><span>...</span></li>';

            $html .= '<li class="next">';
            $url = $baseUrl . '?' . http_build_query(array('p' => $pageInfo['page'] + 1));
            $html .= '<a href="' . $url . '" data-page="' . ($pageInfo['page'] + 1) . '">&gt;</a>';
            $html .= '</li>';

            $html .= '<li class="last">';
            $url = $baseUrl . '?' . http_build_query(array('p' => $pageInfo['pages']));
            $html .= '<a href="' . $url . '" data-page="' . $pageInfo['pages'] . '">&gt;&gt;</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

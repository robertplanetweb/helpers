<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

function dmp($text) {
    echo '<pre>';
    var_dump($text);
    echo '</pre>';
}

function ddmp($text) {
    dmp($text);
    die();
}

function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function is_me() {
    return file_get_contents(base_path()."/ip.txt") == $_SERVER["REMOTE_ADDR"] || $_SERVER["REMOTE_ADDR"] == '127.0.0.1';
}

function groupBy($items, $key = 'id') {
    $newItems = [];
    foreach ($items as $item) {
        if (is_object($item)) {
            $newItems[$item->{$key}][] = $item;
        } else {
            $newItems[$item[$key]][] = $item;
        }
    }
    return $newItems;
}

function keyBy($items, $key = 'id') {
    $newItems = [];
    foreach ($items as $item) {
        if (is_object($item)) {
            $newItems[$item->{$key}] = $item;
        } else {
            $newItems[$item[$key]] = $item;
        }
    }
    return $newItems;
}

function pluck($items, $key = 'id') {
    $plucked = [];
    foreach ($items as $item) {
        if (is_object($item)) {
            $plucked[] = $item->{$key};
        } else {
            $plucked[] = $item[$key];
        }
    }

    return $plucked;
}

function spread($arr, $spread) {
    foreach ( $spread as $key => $value ) {
        if ( is_object($arr) ) {
            $arr->$key = $value;
        }
        else {
            $arr[$key] = $value;
        }
    }

    return $arr;
}

function toArray($var) {
    return json_decode(json_encode($var), true);
}

function start_query_log() {
    DB::enableQueryLog();
}

function get_query_log() {
    dmp(DB::getQueryLog());
}

function yes_no($var, $value = null, $red = false) {
    $yes = '<span class="color-green">Yes</span>';
    $no = $red ? '<span class="color-red">No</span>' : 'No';

    if ( !$value ) {
        return $var ? $yes : $no;
    }

    return $var === $value ? $yes : $no;
}

function checked($var, $val) {
    if ( is_array($val) ) {
        return in_array($var, $val) ? 'checked' : '';
    }
    return ($var == $val ? 'checked' : '');
}

function selected($var, $val) {
    return ($var == $val ? 'selected="selected"' : '');
}

function get_controller_name() {
    $routeArray = app('request')->route()->getAction();
    $controllerAction = class_basename($routeArray['controller']);
    list($controllerName, $actionName) = explode('@', $controllerAction);
    return str_replace('_', '-', Str::snake((str_replace('Controller', '', $controllerName))));
}

function get_thumb_url($url, $size = false) {
    if ( !$url ) {
        return false;
    }

    $pathinfo = pathinfo($url);
    $thumb = '_thumb';

    if ( $size == 'original' ) {
        $thumb = '_original';
    }
    else if ( $size ) {
        $thumb = '_thumb_'.$size;
    }

    return config('app.uploads_url').str_replace($pathinfo['filename'].'.'.$pathinfo['extension'], $pathinfo['filename'].$thumb.'.'.$pathinfo['extension'], $url);
}

function get_thumb_path($url, $size = false) {
    if ( !$url ) {
        return false;
    }

    $pathinfo = pathinfo($url);
    $thumb = '_thumb';

    if ( $size == 'original' ) {
        $thumb = '_original';
    }
    else if ( $size ) {
        $thumb = '_thumb_'.$size;
    }

    return config('app.uploads_path').str_replace($pathinfo['filename'].'.'.$pathinfo['extension'], $pathinfo['filename'].$thumb.'.'.$pathinfo['extension'], $url);
}

function make_media_thumb($image_name, $type, $thumb) {
    $thumb_size = config('app.thumb_sizes.'.$type.'.'.$thumb);

    if ( !$thumb_size[0] && !$thumb_size[1] ) {
        return;
    }

    if (extension_loaded('imagick') ) {
        Image::configure(array('driver' => 'imagick'));
    }

    $original_path = config('app.uploads_path').'/'.$type.'/'.$image_name;
    $pathinfo = pathinfo($image_name);
    $thumb_path = config('app.uploads_path').'/'.$type.'/'.$pathinfo['filename'].($thumb == 'original' ? '' : '_'.$thumb).'.'.$pathinfo['extension'];

    if ( !file_exists($original_path) ) {
        return;
    }

    copy($original_path, $thumb_path);

    Image::make($thumb_path)->fit($thumb_size[0], $thumb_size[1])->save(null, 100);
}

function get_youtube_id($url) {
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
    return $matches[1];
}

function get_filters($filters) {
    if ( !$filters ) {
        return '';
    }

    $string = '';
    foreach ( $filters as $key => $value ) {
        $string .= '&'.$key.'='.$value;
    }

    return $string;
}

function get_sorting_url($filters, $order_by, $default = 'asc') {
    $url = URL::current().'?';

    foreach ( $filters as $key => $value ) {
        if ( $value === '' ) continue;
        if ( in_array($key, ['order_by', 'order', 'page', '_url']) ) continue;

        $url .= $key.'='.$value.'&';
    }

    $url .= '&order_by='.$order_by;

    if ( isset($filters['order']) && $filters['order_by'] == $order_by ) {
        $url .= '&order='.($filters['order'] == 'asc' ? 'desc' : 'asc');
    }
    else {
        $url .= '&order='.$default;
    }

    return $url;
}

function get_sorting_html($filters, $order_by) {
    if ( isset($filters['order_by']) &&  $filters['order_by'] == $order_by ) {
        if ( isset($filters['order']) && $filters['order'] == 'desc' ) {
            return '<i class="ion-chevron-down"></i>';
        }

        return '<i class="ion-chevron-up"></i>';
    }

    return '<span><i class="ion-chevron-up"></i><i class="ion-chevron-down"></i></span>';
}

function ints_to_strings($arr) {
    foreach( $arr as &$var ) {
        $var = (string) $var;
    }

    return $arr;
}

function pretty_hour($hour) {
    $pretty_hour = $hour.' AM';

    if ( $hour > 11 ) {
        $pretty_hour = $hour == 12 ? '12 PM' : ($hour - 12).' PM';
    }
    else if ( $hour == 0 ) {
        $pretty_hour = '12 AM';
    }

    return $pretty_hour;
}

function format_date($date) {
    $year = date('Y', strtotime($date));

    if ( $year != date('Y') ) {
        return date('d M Y', strtotime($date));
    }

    return date('d M', strtotime($date));
}

function get_first_last_name($name) {
    $exp = explode(' ', $request->get('name'));
    $last_name = array_pop($exp);
    $first_name = implode(' ', $exp);

    return compact('first_name', 'last_name');
}

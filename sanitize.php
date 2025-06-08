function sanitize($input) {
    if (is_array($input)) {
        $sanitizedArray = [];
        foreach ($input as $key => $value) $sanitizedArray[$key] = sanitize($value);
        return $sanitizedArray;
    } else return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

const sanitizePattern = /\p{Mn}|[^a-zA-Z0-9-]/gu;

function sanitize(string) {
    return string
        .normalize("NFD")
        .replaceAll(/\s+/g, '-')
        .replaceAll(sanitizePattern, '')
        .toLowerCase();
}

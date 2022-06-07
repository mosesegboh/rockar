function htmlToText(html) {
    const text = new DOMParser().parseFromString(html, 'text/html');
    return text.body.textContent || '';
}

export default (string) => htmlToText(string);

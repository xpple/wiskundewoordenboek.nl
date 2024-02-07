const labels = document.getElementsByTagName('label');
for (const label of labels) {
    const input = document.getElementById(label.htmlFor);
    if (input?.hasAttribute('required') && input?.getAttribute('type') !== 'search') {
        label.innerText += '*';
    }
}

marked.use({
    renderer: {
        codespan(code) {
            return `<code class="inline">${code}</code>`;
        },

        code(code, infostring, escaped) {
            return `<code class="block">${code}</code>`;
        }
    }
});

for (const element of document.getElementsByClassName("render-markdown")) {
    element.innerHTML = marked.parse(element.innerHTML);
}

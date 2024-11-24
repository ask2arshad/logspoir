function searchAndHighlight() {
    let searchText = document.getElementById('searchInput').value;
    let content = document.body.innerHTML;

    // Reset any previous highlights
    content = content.replace(/<span class="highlight">/g, "").replace(/<\/span>/g, "");

    // Highlight all occurrences of the search text
    if (searchText) {
        let regex = new RegExp(`(${searchText})`, 'gi');
        content = content.replace(regex, '<span class="highlight">$1</span>');
        document.body.innerHTML = content;
        
        // Scroll to the first highlighted element
        const firstHighlighted = document.querySelector('.highlight');
        if (firstHighlighted) {
            firstHighlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

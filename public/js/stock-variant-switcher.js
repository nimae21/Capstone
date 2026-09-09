(() => {
    const select = document.getElementById('variantColor');
    if (!select) return;
    const groups = document.querySelectorAll('.variant-switcher .size-switcher');
    const showSelectedColor = () => {
        groups.forEach(group => { group.hidden = group.dataset.color !== select.value; });
    };
    select.addEventListener('change', showSelectedColor);
    window.addEventListener('pageshow', showSelectedColor);
    showSelectedColor();
})();

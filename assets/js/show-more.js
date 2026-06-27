/**
 * Show More / Thu gọn — toggle hiện/ẩn sản phẩm trong section
 */
function toggleShowMore(sectionClass, btn) {
    var items = document.querySelectorAll('.' + sectionClass);
    var isHidden = items.length > 0 && items[0].style.display === 'none';

    if (isHidden) {
        items.forEach(function(el) {
            el.style.display = '';
            el.classList.remove('more-item');
        });
        btn.querySelector('span').textContent = 'Thu gọn';
        btn.querySelector('i').className = 'fas fa-chevron-up';
    } else {
        items.forEach(function(el) {
            el.style.display = 'none';
            el.classList.add('more-item');
        });
        btn.querySelector('span').textContent = 'Xem thêm';
        btn.querySelector('i').className = 'fas fa-chevron-down';
    }
}

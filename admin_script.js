document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const target = button.getAttribute('data-target');

            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });

    document.querySelectorAll('.edit-user').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-user-id').value = btn.dataset.id;
            document.getElementById('edit-user-name').value = btn.dataset.name;
            document.getElementById('edit-user-email').value = btn.dataset.email;
            document.getElementById('edit-user-password').value = '';
            document.getElementById('user-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.edit-product').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-product-id').value = btn.dataset.id;
            document.getElementById('edit-product-name').value = btn.dataset.name;
            document.getElementById('edit-product-price').value = btn.dataset.price;
            document.getElementById('edit-product-category').value = btn.dataset.category;
            document.getElementById('product-modal').style.display = 'block';
        });
    });

    document.getElementById('add-product-button')?.addEventListener('click', () => {
        document.getElementById('edit-product-id').value = '';
        document.getElementById('edit-product-name').value = '';
        document.getElementById('edit-product-price').value = '';
        document.getElementById('edit-product-category').value = 'hot-beverages';
        document.getElementById('product-modal').style.display = 'block';
    });

    document.querySelectorAll('.modal .close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal').style.display = 'none';
        });
    });
});

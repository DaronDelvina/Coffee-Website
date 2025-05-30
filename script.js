const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");

menuOpenButton.addEventListener("click", () => {
    document.body.classList.toggle("show-mobile-menu");
});

menuCloseButton.addEventListener("click", () => menuOpenButton.click());

// Shopping cart functionality
let cart = [];

document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = document.body.classList.contains('logged-in');

    if (isLoggedIn) {
        // Wait until #cart-items exists before initializing
        const waitForCart = setInterval(() => {
            if (document.getElementById('cart-items')) {
                clearInterval(waitForCart);
                initializeCart();
            }
        }, 100);
    }
});

function initializeCart() {
    fetch('get_cart.php')
        .then(response => response.json())
        .then(data => {
            cart = data;
            console.log("Cart loaded from DB:", cart); // Debug
            updateCartDisplay();
            initializeAddToCartButtons();
            initializeCheckoutButton();
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            initializeAddToCartButtons();
            initializeCheckoutButton();
        });
}

function initializeAddToCartButtons() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    addToCartButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
    });

    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
            const productCard = this.closest('.product-card');
            const productId = parseInt(productCard.getAttribute('data-id'));
            const productName = productCard.getAttribute('data-name');
            const productPrice = parseFloat(productCard.getAttribute('data-price'));
            const quantityInput = productCard.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value);

            addToCart(productId, productName, productPrice, quantity);
        });
    });
}

function initializeCheckoutButton() {
    const checkoutButton = document.getElementById('checkout-button');
    if (checkoutButton) {
        const newCheckoutButton = checkoutButton.cloneNode(true);
        checkoutButton.parentNode.replaceChild(newCheckoutButton, checkoutButton);

        document.getElementById('checkout-button').addEventListener('click', function () {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            processCheckout();
        });
    }
}

function addToCart(id, name, price, quantity) {
    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: quantity
        });
    }

    // Save to DB
    const formData = new FormData();
    formData.append('product_id', id);
    formData.append('quantity', existingItem ? existingItem.quantity : quantity);

    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => console.log("update_cart.php says:", text));

    updateCartDisplay();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);

    const formData = new FormData();
    formData.append('product_id', id);
    formData.append('quantity', 0); // Quantity 0 = delete from DB

    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => console.log("remove update_cart.php says:", text));

    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsList = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    if (!cartItemsList || !cartTotal) return;

    cartItemsList.innerHTML = '';

    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        const listItem = document.createElement('li');
        listItem.className = 'cart-item';
        listItem.innerHTML = `
            <span class="item-name">${item.name}</span>
            <span class="item-details">
                ${item.quantity} x $${item.price.toFixed(2)} = $${itemTotal.toFixed(2)}
            </span>
            <button class="remove-item" onclick="removeFromCart(${item.id})">Remove</button>
        `;

        cartItemsList.appendChild(listItem);
    });

    cartTotal.textContent = `Total: $${total.toFixed(2)}`;

    const cartElement = document.getElementById('cart');
    if (cartElement) {
        cartElement.style.display = cart.length > 0 ? 'block' : 'none';
    }
}

function processCheckout() {
    const checkoutButton = document.getElementById('checkout-button');

    checkoutButton.disabled = true;
    checkoutButton.textContent = 'Processing...';

    let cartData = '';
    cart.forEach((item, index) => {
        if (index > 0) cartData += '|';
        cartData += `${item.id},${item.name},${item.price},${item.quantity}`;
    });

    const formData = new FormData();
    formData.append('cart_data', cartData);
    formData.append('ajax', '1');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'checkout.php', true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response = xhr.responseText.trim();
                const parts = response.split('|');
                const status = parts[0];

                if (status === 'success') {
                    const orderId = parts[2];
                    showThankYouMessage(orderId);
                    cart = [];

                    initializeAddToCartButtons();
                } else if (status === 'error') {
                    alert('Error: ' + parts[1]);
                } else {
                    alert('Unexpected response from server');
                }
            } else {
                alert('An error occurred while processing your order. Please try again.');
            }

            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Checkout';
        }
    };

    xhr.send(formData);
}

function showThankYouMessage(orderId) {
    const cartElement = document.getElementById('cart');

    cartElement.innerHTML = `
        <div class="small-thank-you">
            <div class="success-icon">✓</div>
            <h4>Thank You!</h4>
            <p>Order placed successfully</p>
            <button id="close-thank-you" class="close-btn">×</button>
        </div>
    `;

    cartElement.style.display = 'block';

    document.getElementById('close-thank-you').addEventListener('click', function () {
        cartElement.style.display = 'none';
        resetCartDisplay();
    });

    setTimeout(function () {
        if (cartElement.style.display !== 'none') {
            cartElement.style.display = 'none';
            resetCartDisplay();
        }
    }, 5000);
}

function resetCartDisplay() {
    const cartElement = document.getElementById('cart');
    cartElement.innerHTML = `
        <h3>Your Cart</h3>
        <ul id="cart-items"></ul>
        <p id="cart-total">Total: $0.00</p>
        <button id="checkout-button">Checkout</button>
    `;

    initializeCheckoutButton();
}
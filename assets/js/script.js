function increaseQuantity() {
    const quantity = document.getElementById("quantity");
    const cartQuantity = document.getElementById("cart-quantity");

    const max = parseInt(quantity.max);
    const current = parseInt(quantity.value);

    if (current < max) {
        quantity.value = current + 1;
        cartQuantity.value = quantity.value;
    }
}

function decreaseQuantity() {
    const quantity = document.getElementById("quantity");
    const cartQuantity = document.getElementById("cart-quantity");

    const current = parseInt(quantity.value);

    if (current > 1) {
        quantity.value = current - 1;
        cartQuantity.value = quantity.value;
    }
}
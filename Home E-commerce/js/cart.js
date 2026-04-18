/* ===== CART STATE MANAGEMENT ===== */
class CartManager {
  constructor() {
    this.cart = this.loadCart();
    this.isSeller = this.loadSellerMode();
  }

  loadCart() {
    const saved = localStorage.getItem('cart');
    return saved ? JSON.parse(saved) : [];
  }

  loadSellerMode() {
    return localStorage.getItem('sellerMode') === 'true';
  }

  saveCart() {
    localStorage.setItem('cart', JSON.stringify(this.cart));
    this.updateCartUI();
    this.dispatchCartEvent();
  }

  saveSellerMode(isSeller) {
    this.isSeller = isSeller;
    localStorage.setItem('sellerMode', isSeller);
  }

  addToCart(product, quantity = 1) {
    const existingItem = this.cart.find(item => item.id === product.id);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      this.cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        quantity: quantity,
        sku: product.sku,
        category: product.category
      });
    }

    this.saveCart();
    this.showNotification(`Added ${quantity}x ${product.name} to cart`);
  }

  removeFromCart(productId) {
    this.cart = this.cart.filter(item => item.id !== productId);
    this.saveCart();
  }

  updateQuantity(productId, quantity) {
    const item = this.cart.find(item => item.id === productId);
    if (item) {
      item.quantity = Math.max(1, quantity);
      this.saveCart();
    }
  }

  getCartTotal() {
    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
  }

  getCartItemsCount() {
    return this.cart.reduce((count, item) => count + item.quantity, 0);
  }

  getDiscount() {
    const total = this.getCartTotal();
    // Volume discount: 5+ items = 10% off
    if (this.getCartItemsCount() >= 5) {
      return total * 0.1;
    }
    return 0;
  }

  getShippingCost() {
    const total = this.getCartTotal();
    if (total > 500) return 0; // Free shipping over $500
    if (total > 200) return 25;
    return 50;
  }

  clearCart() {
    this.cart = [];
    this.saveCart();
  }

  updateCartUI() {
    const badge = document.querySelector('.badge');
    if (badge) {
      badge.textContent = this.getCartItemsCount();
      badge.style.display = this.getCartItemsCount() > 0 ? 'flex' : 'none';
    }
  }

  dispatchCartEvent() {
    const event = new CustomEvent('cartUpdated', {
      detail: { cart: this.cart, total: this.getCartTotal() }
    });
    document.dispatchEvent(event);
  }

  showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: var(--success);
      color: white;
      padding: 12px 20px;
      border-radius: 4px;
      z-index: 2000;
      animation: slideIn 0.3s ease-out;
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
  }
}

/* ===== GLOBAL CART INSTANCE ===== */
const cartManager = new CartManager();

/* ===== INITIALIZE ===== */
document.addEventListener('DOMContentLoaded', () => {
  cartManager.updateCartUI();
  initializeEventListeners();
});

function initializeEventListeners() {
  // Cart toggle
  const cartBtn = document.querySelector('[data-cart-btn]');
  const miniCart = document.querySelector('.mini-cart');
  const closeCart = document.querySelector('.close-cart');

  if (cartBtn) {
    cartBtn.addEventListener('click', () => {
      miniCart?.classList.toggle('open');
      renderMiniCart();
    });
  }

  if (closeCart) {
    closeCart.addEventListener('click', () => {
      miniCart?.classList.remove('open');
    });
  }

  // Contractor toggle
  const sellerToggle = document.querySelector('[data-seller-toggle]');
  if (sellerToggle) {
    sellerToggle.checked = cartManager.isSeller;
    sellerToggle.addEventListener('change', e => {
      cartManager.saveSellerMode(e.target.checked);
      location.reload();
    });
  }

  // Add to cart buttons
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-add-to-cart]')) {
      const btn = e.target.closest('[data-add-to-cart]');
      const productId = btn.dataset.productId;
      const product = getProductById(productId);
      if (product) {
        cartManager.addToCart(product);
      }
    }
  });
}

function renderMiniCart() {
  const container = document.querySelector('.mini-cart-items');
  if (!container) return;

  container.innerHTML = '';

  if (cartManager.cart.length === 0) {
    container.innerHTML = '<p style="text-align: center; color: var(--text-light); padding: var(--spacing-lg);">Your cart is empty</p>';
    return;
  }

  cartManager.cart.forEach(item => {
    const itemEl = document.createElement('div');
    itemEl.className = 'cart-item';
    itemEl.innerHTML = `
      <div class="cart-item-image">
        <img src="${item.image || PLACEHOLDER_IMAGE}" alt="${item.name}" />
      </div>
      <div class="cart-item-details">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-sku">SKU: ${item.sku}</div>
        <div class="cart-item-quantity">
          <button class="qty-btn" data-qty-decrease="${item.id}">−</button>
          <span class="qty-display">${item.quantity}</span>
          <button class="qty-btn" data-qty-increase="${item.id}">+</button>
        </div>
        <div class="cart-item-price">$${(item.price * item.quantity).toFixed(2)}</div>
        <div class="remove-item" data-remove-item="${item.id}">Remove</div>
      </div>
    `;
    container.appendChild(itemEl);

    // Quantity controls
    itemEl.querySelector(`[data-qty-decrease]`).addEventListener('click', () => {
      cartManager.updateQuantity(item.id, item.quantity - 1);
      renderMiniCart();
    });

    itemEl.querySelector(`[data-qty-increase]`).addEventListener('click', () => {
      cartManager.updateQuantity(item.id, item.quantity + 1);
      renderMiniCart();
    });

    itemEl.querySelector(`[data-remove-item]`).addEventListener('click', () => {
      cartManager.removeFromCart(item.id);
      renderMiniCart();
    });
  });

  updateCartSummary();
}

function updateCartSummary() {
  const summary = document.querySelector('.mini-cart-summary');
  if (!summary) return;

  const subtotal = cartManager.getCartTotal();
  const discount = cartManager.getDiscount();
  const shipping = cartManager.getShippingCost();
  const total = subtotal - discount + shipping;

  summary.innerHTML = `
    <div class="summary-row">
      <span>Subtotal (${cartManager.getCartItemsCount()} items)</span>
      <span>$${subtotal.toFixed(2)}</span>
    </div>
    ${discount > 0 ? `
      <div class="summary-row" style="color: var(--success);">
        <span>Volume Discount</span>
        <span>-$${discount.toFixed(2)}</span>
      </div>
    ` : ''}
    <div class="summary-row">
      <span>Shipping</span>
      <span>${shipping === 0 ? 'FREE' : '$' + shipping.toFixed(2)}</span>
    </div>
    <div class="summary-row total">
      <span>Total</span>
      <span>$${total.toFixed(2)}</span>
    </div>
    <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--medium-gray); font-size: 0.85rem; color: var(--text-light);">
      ${cartManager.isSeller ? '<strong>Seller Mode:</strong> Net-30 terms available' : ''}
    </div>
  `;
}

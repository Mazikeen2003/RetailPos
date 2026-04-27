function peso(value) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
  }).format(Number(value || 0));
}

export default function CartPanel({
  cart,
  discountType,
  discountAmount,
  subtotal,
  total,
  paying,
  currentTime,
  onDiscountChange,
  onQuantityChange,
  onRemove,
  onCancel,
  onPay,
}) {
  return (
    <section className="panel sale-panel">
      <div className="panel-heading">
        <div>
          <p className="eyebrow">Current Sale</p>
          <h2>Cart</h2>
          <p className="sale-help">`Void Item` is beside each product. `Cancel Sale` is the button below totals.</p>
        </div>
        <div className="lane-indicator">
          <span>Lane 03</span>
          <strong>{currentTime}</strong>
        </div>
      </div>

      {!cart.length ? (
        <div className="panel-empty">Cart is empty. Search or scan a product to start.</div>
      ) : (
        <div className="cart-list">
          {cart.map((item) => (
            <div key={item.id} className="cart-item">
              <div>
                <strong>{item.name}</strong>
                <p>{item.barcode}</p>
              </div>
              <div className="cart-item-meta">
                <div className="qty-controls">
                  <button type="button" onClick={() => onQuantityChange(item.id, item.qty - 1)}>
                    -
                  </button>
                  <span>{item.qty}</span>
                  <button type="button" onClick={() => onQuantityChange(item.id, item.qty + 1)}>
                    +
                  </button>
                </div>
                <strong>{peso(item.qty * item.price)}</strong>
                <button type="button" className="link-danger" onClick={() => onRemove(item.id)}>
                  Void Item
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      <label className="field discount-field">
        <span>Discount</span>
        <select value={discountType} onChange={(event) => onDiscountChange(event.target.value)}>
          <option value="none">No Discount</option>
          <option value="senior">Senior Citizen (20%)</option>
          <option value="pwd">PWD (20%)</option>
          <option value="athlete">Athlete (10%)</option>
          <option value="solo">Solo Parent (10%)</option>
        </select>
      </label>

      <div className="totals-box">
        <div>
          <span>Subtotal</span>
          <strong>{peso(subtotal)}</strong>
        </div>
        <div>
          <span>Discount</span>
          <strong>{peso(discountAmount)}</strong>
        </div>
        <div className="total-line">
          <span>Total</span>
          <strong>{peso(total)}</strong>
        </div>
      </div>

      <div className="action-row pos-actions">
        <button type="button" className="btn btn-secondary" onClick={onCancel} disabled={!cart.length || paying}>
          Cancel Sale
        </button>
        <button type="button" className="btn btn-primary" onClick={onPay} disabled={!cart.length || paying}>
          {paying ? "Processing..." : "Pay"}
        </button>
      </div>
    </section>
  );
}

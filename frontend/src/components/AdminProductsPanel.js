function normalizeForm(product) {
  if (!product) {
    return {
      name: "",
      barcode: "",
      category: "",
      price: "",
      stock: "",
      active: true,
    };
  }

  return {
    name: product.name,
    barcode: product.barcode,
    category: product.category,
    price: String(product.price),
    stock: String(product.stock),
    active: Boolean(product.active),
  };
}

export default function AdminProductsPanel({
  products,
  categories,
  productForm,
  productErrors,
  editingProductId,
  setProductForm,
  onSubmit,
  onEdit,
  onCancelEdit,
  onToggleActive,
}) {
  const updateField = (field, value) => {
    setProductForm((current) => ({ ...current, [field]: value }));
  };

  return (
    <section className="panel product-management-panel">
      <div className="panel-heading">
        <div>
          <p className="eyebrow">Administrator</p>
          <h2>Manage Products</h2>
          <p className="panel-empty">Use Edit to change stock or price. Use the status button to deactivate or reactivate a product.</p>
        </div>
      </div>

      {!editingProductId && (
        <form className="admin-form-grid" onSubmit={onSubmit}>
          <label className="field">
            <span>Product Name</span>
            <input
              value={productForm.name}
              onChange={(event) => updateField("name", event.target.value)}
            />
            {productErrors.name ? <small className="field-error">{productErrors.name}</small> : null}
          </label>
          <label className="field">
            <span>Barcode</span>
            <input
              value={productForm.barcode}
              onChange={(event) => updateField("barcode", event.target.value)}
            />
            {productErrors.barcode ? <small className="field-error">{productErrors.barcode}</small> : null}
          </label>
        <label className="field">
          <span>Category</span>
          <select
            value={productForm.category}
            onChange={(event) => updateField("category", event.target.value)}
          >
            <option value="">Select a category</option>
            {categories.map((category) => (
              <option key={category} value={category}>
                {category}
              </option>
            ))}
          </select>
          {productErrors.category ? <small className="field-error">{productErrors.category}</small> : null}
        </label>
          <label className="field">
            <span>Price</span>
            <input
              type="number"
              min="0"
              step="0.01"
              value={productForm.price}
              onChange={(event) => updateField("price", event.target.value)}
            />
            {productErrors.price ? <small className="field-error">{productErrors.price}</small> : null}
          </label>
          <label className="field">
            <span>Stock Quantity</span>
            <input
              type="number"
              min="0"
              step="1"
              value={productForm.stock}
              onChange={(event) => updateField("stock", event.target.value)}
            />
            {productErrors.stock ? <small className="field-error">{productErrors.stock}</small> : null}
          </label>
          <label className="checkbox-field">
            <input
              type="checkbox"
              checked={productForm.active}
              onChange={(event) => setProductForm((current) => ({ ...current, active: event.target.checked }))}
            />
            <span>Active product</span>
          </label>
          <button type="submit" className="btn btn-primary">
            Add Product
          </button>
        </form>
      )}

      <div className="table-like">
        {products.map((product) => (
          <div key={product.id} className="table-row product-management-row">
            <div>
              <strong>{product.name}</strong>
              <p>{product.barcode} • {product.category}</p>
            </div>
            <div className="table-row-actions product-management-actions">
              <span className={`status-pill ${product.active ? "ok" : "off"}`}>
                {product.active ? "Active" : "Inactive"}
              </span>
              <span className="product-stock-count">{product.stock} stock</span>
              <span className="product-price">PHP {Number(product.price).toFixed(2)}</span>
              <button type="button" className="btn btn-secondary slim" onClick={() => onEdit(normalizeForm(product), product.id)}>
                Edit
              </button>
              <button type="button" className="btn btn-secondary slim" onClick={() => onToggleActive(product)}>
                {product.active ? "Deactivate" : "Reactivate"}
              </button>
            </div>
          </div>
        ))}
      </div>

      {editingProductId && (
        <div className="modal-overlay" role="presentation" onClick={onCancelEdit}>
          <div
            className="modal-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-product-title"
            onClick={(event) => event.stopPropagation()}
          >
            <div className="modal-heading">
              <div>
                <p className="eyebrow">Administrator</p>
                <h2 id="edit-product-title">Edit Product</h2>
                <p className="panel-empty">Update the product details, stock, and status here.</p>
              </div>
              <button type="button" className="btn btn-secondary slim" onClick={onCancelEdit}>
                Close
              </button>
            </div>

            <form className="admin-form-grid modal-form-grid" onSubmit={onSubmit}>
              <label className="field">
                <span>Product Name</span>
                <input
                  value={productForm.name}
                  onChange={(event) => updateField("name", event.target.value)}
                />
                {productErrors.name ? <small className="field-error">{productErrors.name}</small> : null}
              </label>
              <label className="field">
                <span>Barcode</span>
                <input
                  value={productForm.barcode}
                  onChange={(event) => updateField("barcode", event.target.value)}
                />
                {productErrors.barcode ? <small className="field-error">{productErrors.barcode}</small> : null}
              </label>
              <label className="field">
                <span>Category</span>
                <select
                  value={productForm.category}
                  onChange={(event) => updateField("category", event.target.value)}
                >
                  <option value="">Select a category</option>
                  {categories.map((category) => (
                    <option key={category} value={category}>
                      {category}
                    </option>
                  ))}
                </select>
                {productErrors.category ? <small className="field-error">{productErrors.category}</small> : null}
              </label>
              <label className="field">
                <span>Price</span>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={productForm.price}
                  onChange={(event) => updateField("price", event.target.value)}
                />
                {productErrors.price ? <small className="field-error">{productErrors.price}</small> : null}
              </label>
              <label className="field">
                <span>Stock Quantity</span>
                <input
                  type="number"
                  min="0"
                  step="1"
                  value={productForm.stock}
                  onChange={(event) => updateField("stock", event.target.value)}
                />
                {productErrors.stock ? <small className="field-error">{productErrors.stock}</small> : null}
              </label>
              <label className="checkbox-field">
                <input
                  type="checkbox"
                  checked={productForm.active}
                  onChange={(event) => setProductForm((current) => ({ ...current, active: event.target.checked }))}
                />
                <span>Active product</span>
              </label>
              <div className="modal-actions">
                <button type="button" className="btn btn-secondary" onClick={onCancelEdit}>
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary">
                  Save Product
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </section>
  );
}

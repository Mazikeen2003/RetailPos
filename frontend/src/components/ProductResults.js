function peso(value) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
  }).format(Number(value || 0));
}

export default function ProductResults({
  products,
  loading,
  onAdd,
  categories,
  activeCategory,
  onCategoryChange,
}) {
  if (loading) {
    return <div className="panel-empty">Loading products...</div>;
  }

  return (
    <>
      <div className="category-strip">
        {categories.map((category) => (
          <button
            key={category}
            type="button"
            className={`category-chip ${activeCategory === category ? "active" : ""}`}
            onClick={() => onCategoryChange(category)}
          >
            {category}
          </button>
        ))}
      </div>

      {!products.length ? (
        <div className="panel-empty">No products matched your search.</div>
      ) : (
        <div className="product-grid">
          {products.map((product) => (
            <button
              key={product.id}
              type="button"
              className={`product-card ${product.active ? "" : "inactive"}`}
              onClick={() => onAdd(product)}
              disabled={!product.active || product.stock <= 0}
            >
              <div className="product-card-top">
                <span className="product-category">{product.category}</span>
                <span className={`stock-badge ${product.stock <= 10 ? "danger" : "safe"}`}>
                  {product.stock} in stock
                </span>
              </div>
              <strong>{product.name}</strong>
              <p>{product.barcode}</p>
              <div className="product-card-bottom">
                <span>{peso(product.price)}</span>
                <span className="product-cta">
                  {!product.active ? "Inactive" : product.stock <= 0 ? "Out of stock" : "Add"}
                </span>
              </div>
            </button>
          ))}
        </div>
      )}
    </>
  );
}

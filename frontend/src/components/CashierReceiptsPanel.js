function peso(value) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
  }).format(Number(value || 0));
}

export default function CashierReceiptsPanel({ sales, onReprint }) {
  const lastSale = sales[0];

  return (
    <section className="panel">
      <div className="panel-heading">
        <div>
          <p className="eyebrow">Cashier</p>
          <h2>Reprint Last Receipt</h2>
        </div>
      </div>

      {!lastSale ? (
        <div className="panel-empty">No completed transaction yet.</div>
      ) : (
        <div className="receipt-card thermal-receipt">
          <div className="receipt-headline">
            <strong>REPRINT</strong>
            <span>Sale #{lastSale.id}</span>
          </div>
          <div className="receipt-meta-grid">
            <span>Cashier</span>
            <strong>{lastSale.cashier?.name || "Unknown cashier"}</strong>
            <span>Items</span>
            <strong>{lastSale.items.length}</strong>
          </div>
          <div className="list-stack compact">
            {lastSale.items.map((item) => (
              <div key={item.id} className="list-row">
                <span>{item.product_name} x {item.quantity}</span>
                <span>{peso(item.line_total)}</span>
              </div>
            ))}
          </div>
          <div className="total-line">
            <span>Total</span>
            <strong>{peso(lastSale.total)}</strong>
          </div>
          <div className="receipt-meta-grid">
            <span>VATable Sales</span>
            <strong>{peso(lastSale.vatable_sales)}</strong>
            <span>VAT 12%</span>
            <strong>{peso(lastSale.vat_amount)}</strong>
          </div>
          <button type="button" className="btn btn-primary" onClick={() => onReprint(lastSale)}>
            Print / Save PDF
          </button>
        </div>
      )}
    </section>
  );
}

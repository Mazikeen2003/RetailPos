import { useEffect, useMemo, useState } from "react";
import AdminProductsPanel from "../components/AdminProductsPanel";
import AdminUsersPanel from "../components/AdminUsersPanel";
import BarcodePad from "../components/BarcodePad";
import CartPanel from "../components/CartPanel";
import CashierReceiptsPanel from "../components/CashierReceiptsPanel";
import KpiCard from "../components/KpiCard";
import NoticeBanner from "../components/NoticeBanner";
import ProductResults from "../components/ProductResults";
import RoleTabs from "../components/RoleTabs";
import { createAuditLog, getAuditLogs } from "../services/auditService";
import { authorizeSupervisor } from "../services/authService";
import { getDashboardSummary } from "../services/dashboardService";
import { createProduct, getProducts, updateProduct } from "../services/productService";
import { createSale, getSales } from "../services/salesService";
import { createUser, getUserMeta, getUsers, updateUser } from "../services/userService";

const discountRates = {
  none: 0,
  senior: 0.2,
  pwd: 0.2,
  athlete: 0.1,
  solo: 0.1,
};

const vatRate = 0.12;

const defaultProductForm = {
  name: "",
  barcode: "",
  category: "",
  price: "",
  stock: "",
  active: true,
};

const defaultUserForm = {
  name: "",
  email: "",
  password: "",
  role_id: "",
  is_active: true,
};

const defaultProductCategories = [
  "Beverage",
  "Canned Goods",
  "Grocery",
  "Personal Care",
  "Snacks",
];

function peso(value) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
  }).format(Number(value || 0));
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function printReceipt(sale) {
  const receiptWindow = window.open("", "receipt-print", "width=420,height=720");

  if (!receiptWindow) {
    return false;
  }

  const itemRows = sale.items
    .map((item) => `
      <tr>
        <td>
          <strong>${escapeHtml(item.product_name)}</strong>
          <span>${escapeHtml(item.barcode)}</span>
        </td>
        <td class="qty">${item.quantity}</td>
        <td class="amount">${peso(item.line_total)}</td>
      </tr>
    `)
    .join("");

  receiptWindow.document.write(`
    <!doctype html>
    <html>
      <head>
        <title>Receipt - Sale #${sale.id}</title>
        <style>
          @page { size: 80mm auto; margin: 8mm; }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            color: #111827;
            font-family: "Courier New", monospace;
            font-size: 12px;
            background: #fff;
          }
          .receipt {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
          }
          .center { text-align: center; }
          h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.08em;
          }
          .muted { color: #4b5563; }
          .meta, .totals {
            border-top: 1px dashed #111827;
            border-bottom: 1px dashed #111827;
            margin: 12px 0;
            padding: 8px 0;
          }
          .line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 3px 0;
          }
          table {
            width: 100%;
            border-collapse: collapse;
          }
          td {
            padding: 5px 0;
            vertical-align: top;
          }
          td span {
            display: block;
            color: #6b7280;
            font-size: 10px;
          }
          .qty {
            width: 34px;
            text-align: center;
          }
          .amount {
            width: 86px;
            text-align: right;
          }
          .total {
            font-size: 15px;
            font-weight: 800;
          }
          .reprint {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 8px;
            border: 1px solid #111827;
            font-weight: 800;
          }
          .footer {
            margin-top: 14px;
            text-align: center;
          }
          @media print {
            button { display: none; }
          }
        </style>
      </head>
      <body>
        <main class="receipt">
          <div class="center">
            <h1>RETAILPOS</h1>
            <div class="muted">Official Receipt</div>
            <div class="reprint">REPRINT</div>
          </div>

          <section class="meta">
            <div class="line"><span>Sale #</span><strong>${sale.id}</strong></div>
            <div class="line"><span>Cashier</span><strong>${escapeHtml(sale.cashier?.name || "Unknown cashier")}</strong></div>
            <div class="line"><span>Date</span><strong>${new Date(sale.created_at || Date.now()).toLocaleString("en-PH")}</strong></div>
          </section>

          <table>
            <tbody>${itemRows}</tbody>
          </table>

          <section class="totals">
            <div class="line"><span>Subtotal</span><strong>${peso(sale.subtotal)}</strong></div>
            <div class="line"><span>Discount</span><strong>${peso(sale.discount_amount)}</strong></div>
            <div class="line"><span>VATable Sales</span><strong>${peso(sale.vatable_sales)}</strong></div>
            <div class="line"><span>VAT 12%</span><strong>${peso(sale.vat_amount)}</strong></div>
            <div class="line total"><span>Total</span><strong>${peso(sale.total)}</strong></div>
          </section>

          <div class="footer">
            <p>Thank you for your purchase.</p>
          </div>
        </main>
        <script>
          window.onload = function () {
            window.focus();
            window.print();
          };
        </script>
      </body>
    </html>
  `);
  receiptWindow.document.close();

  return true;
}

function getApiErrorMessage(error, fallback) {
  const validationErrors = error?.response?.data?.errors;

  if (validationErrors) {
    const firstValidationMessage = Object.values(validationErrors).flat().find(Boolean);

    if (firstValidationMessage) {
      return firstValidationMessage;
    }
  }

  return error?.response?.data?.message || fallback;
}

export default function PosDashboardPage({ user, onLogout }) {
  const roleName = user.role?.name || "User";
  const isAdmin = roleName === "Admin";
  const isCashier = roleName === "Cashier";
  const isSupervisor = roleName === "Supervisor";
  const initialTab = isAdmin ? "products" : isCashier ? "sales" : "overview";
  const [activeTab, setActiveTab] = useState(initialTab);
  const [dashboard, setDashboard] = useState(null);
  const [products, setProducts] = useState([]);
  const [sales, setSales] = useState([]);
  const [auditLogs, setAuditLogs] = useState([]);
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [search, setSearch] = useState("");
  const [barcode, setBarcode] = useState("");
  const [activeCategory, setActiveCategory] = useState("All");
  const [discountType, setDiscountType] = useState("none");
  const [cart, setCart] = useState([]);
  const [dashboardLoading, setDashboardLoading] = useState(true);
  const [productsLoading, setProductsLoading] = useState(true);
  const [salesLoading, setSalesLoading] = useState(true);
  const [auditLoading, setAuditLoading] = useState(false);
  const [paying, setPaying] = useState(false);
  const [cancelApprovalOpen, setCancelApprovalOpen] = useState(false);
  const [cancelApprovalLoading, setCancelApprovalLoading] = useState(false);
  const [cancelApprovalError, setCancelApprovalError] = useState("");
  const [cancelApprovalForm, setCancelApprovalForm] = useState({
    email: "",
    password: "",
    reason: "",
  });
  const [pageError, setPageError] = useState("");
  const [saleMessage, setSaleMessage] = useState("");
  const [productToast, setProductToast] = useState(null);
  const [productForm, setProductForm] = useState(defaultProductForm);
  const [productErrors, setProductErrors] = useState({});
  const [editingProductId, setEditingProductId] = useState(null);
  const [userForm, setUserForm] = useState(defaultUserForm);
  const [editingUserId, setEditingUserId] = useState(null);

  const tabs = isAdmin
    ? [
        { id: "products", label: "Products" },
        { id: "users", label: "Users" },
        { id: "overview", label: "Overview" },
      ]
    : isCashier
      ? [
          { id: "sales", label: "Sales" },
          { id: "receipts", label: "Receipts" },
        ]
      : [
          { id: "overview", label: "Overview" },
          { id: "activity", label: "Activity" },
          { id: "catalog", label: "Catalog" },
        ];

  const showProductToast = (message) => {
    setProductToast({ id: Date.now(), message });
  };

  useEffect(() => {
    if (!productToast) {
      return undefined;
    }

    const timeoutId = window.setTimeout(() => {
      setProductToast(null);
    }, 2800);

    return () => window.clearTimeout(timeoutId);
  }, [productToast]);

  const refreshDashboard = async () => {
    setDashboardLoading(true);

    try {
      setDashboard(await getDashboardSummary());
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to load dashboard summary.");
    } finally {
      setDashboardLoading(false);
    }
  };

  const refreshProducts = async (term = "") => {
    setProductsLoading(true);

    try {
      setProducts(await getProducts(term));
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to load products.");
    } finally {
      setProductsLoading(false);
    }
  };

  const refreshSales = async () => {
    setSalesLoading(true);

    try {
      setSales(await getSales());
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to load recent transactions.");
    } finally {
      setSalesLoading(false);
    }
  };

  const refreshAuditLogs = async () => {
    setAuditLoading(true);

    try {
      setAuditLogs(await getAuditLogs());
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to load activity logs.");
    } finally {
      setAuditLoading(false);
    }
  };

  useEffect(() => {
    setActiveTab(initialTab);
  }, [initialTab]);

  useEffect(() => {
    refreshDashboard();
    refreshProducts();
    refreshSales();

    if (isAdmin) {
      Promise.all([getUsers(), getUserMeta()])
        .then(([accounts, meta]) => {
          setUsers(accounts);
          setRoles(meta.roles);
        })
        .catch((error) => {
          setPageError(error.response?.data?.message || "Failed to load user accounts.");
        });
    }

    if (isSupervisor) {
      refreshAuditLogs();
    }
  }, [isAdmin, isSupervisor]);

  useEffect(() => {
    if (!isCashier) {
      return undefined;
    }

    const timeoutId = window.setTimeout(() => {
      refreshProducts(search);
    }, 250);

    return () => window.clearTimeout(timeoutId);
  }, [search, isCashier]);

  const subtotal = useMemo(
    () => cart.reduce((sum, item) => sum + Number(item.price) * item.qty, 0),
    [cart]
  );
  const categories = useMemo(() => {
    const values = Array.from(new Set(products.map((product) => product.category))).sort();
    return ["All", ...values];
  }, [products]);
  const adminCategories = useMemo(() => {
    const values = Array.from(
      new Set([...defaultProductCategories, ...products.map((product) => product.category)].filter(Boolean))
    ).sort();

    return values;
  }, [products]);
  const filteredProducts = useMemo(() => {
    if (activeCategory === "All") {
      return products;
    }

    return products.filter((product) => product.category === activeCategory);
  }, [products, activeCategory]);
  const currentTime = new Date().toLocaleTimeString("en-PH", {
    hour: "2-digit",
    minute: "2-digit",
  });
  const discountAmount = subtotal * (discountRates[discountType] || 0);
  const total = subtotal - discountAmount;
  const vatableSales = total / (1 + vatRate);
  const vatAmount = total - vatableSales;
  const metrics = dashboard?.metrics || {
    totalSales: 0,
    transactions: 0,
    activeUsers: 0,
    lowStockAlerts: 0,
  };

  const recordAudit = async (action, details) => {
    try {
      await createAuditLog({ action, details });
    } catch {
      // Keep UI flow responsive even if audit logging fails.
    }
  };

  const handleAddToCart = (product) => {
    setSaleMessage("");
    setPageError("");

    if (!product.active || product.stock <= 0) {
      setPageError(`${product.name} is not available for sale.`);
      return;
    }

    setCart((current) => {
      const existing = current.find((item) => item.id === product.id);

      if (existing) {
        if (existing.qty >= product.stock) {
          setPageError(`Only ${product.stock} unit(s) left for ${product.name}.`);
          return current;
        }

        return current.map((item) =>
          item.id === product.id ? { ...item, qty: item.qty + 1 } : item
        );
      }

      return [
        ...current,
        {
          id: product.id,
          name: product.name,
          barcode: product.barcode,
          price: Number(product.price),
          qty: 1,
          stock: product.stock,
        },
      ];
    });
  };

  const handleQuantityChange = async (productId, nextQty) => {
    const removed = cart.find((item) => item.id === productId);

    setCart((current) =>
      current.flatMap((item) => {
        if (item.id !== productId) {
          return [item];
        }

        if (nextQty <= 0) {
          return [];
        }

        return [{ ...item, qty: Math.min(nextQty, item.stock) }];
      })
    );

    if (nextQty <= 0 && removed) {
      await recordAudit("void_item", `Voided ${removed.name} from the ongoing sale.`);
      setSaleMessage(`${removed.name} was voided from the current sale.`);
    }
  };

  const handleScan = async (nextBarcode = barcode) => {
    setSaleMessage("");
    setPageError("");

    const trimmedBarcode = String(nextBarcode || "").trim();

    if (!trimmedBarcode) {
      return;
    }

    const localMatch = products.find((product) => product.barcode === trimmedBarcode);
    if (localMatch) {
      handleAddToCart(localMatch);
      setBarcode("");
      return;
    }

    try {
      const response = await getProducts(trimmedBarcode);
      const exactMatch = response.find((product) => product.barcode === trimmedBarcode);

      if (!exactMatch) {
        setPageError("Barcode not found.");
        setBarcode("");
        return;
      }

      setProducts(response);
      handleAddToCart(exactMatch);
      setBarcode("");
    } catch (error) {
      setPageError(error.response?.data?.message || "Barcode lookup failed.");
      setBarcode("");
    }
  };

  const handleBarcodePadPress = (key) => {
    if (key === "CLR") {
      setBarcode("");
      return;
    }

    if (key === "DEL") {
      setBarcode((current) => current.slice(0, -1));
      return;
    }

    if (key === "ENTER") {
      handleScan(barcode);
      return;
    }

    setBarcode((current) => `${current}${key}`);
  };

  const handlePay = async () => {
    setPaying(true);
    setSaleMessage("");
    setPageError("");

    try {
      const response = await createSale({
        discountType,
        items: cart.map((item) => ({
          product_id: item.id,
          quantity: item.qty,
        })),
      });

      setSaleMessage(`Sale #${response.sale.id} completed successfully.`);
      setCart([]);
      setDiscountType("none");
      await Promise.all([refreshDashboard(), refreshProducts(search), refreshSales()]);
    } catch (error) {
      setPageError(error.response?.data?.message || "Payment failed.");
    } finally {
      setPaying(false);
    }
  };

  const handleCancelSale = async () => {
    if (!cart.length) {
      return;
    }

    setCancelApprovalOpen(true);
    setCancelApprovalError("");
  };

  const handleCancelApprovalSubmit = async (event) => {
    event.preventDefault();
    setCancelApprovalError("");

    if (!cancelApprovalForm.reason.trim()) {
      setCancelApprovalError("Cancellation reason is required.");
      return;
    }

    setCancelApprovalLoading(true);

    try {
      const approval = await authorizeSupervisor({
        email: cancelApprovalForm.email,
        password: cancelApprovalForm.password,
      });

      await recordAudit(
        "cancel_sale",
        `Cancelled ongoing sale worth ${peso(total)} before payment. Reason: ${cancelApprovalForm.reason}. Approved by ${approval.supervisor?.name || cancelApprovalForm.email}.`
      );

      setCancelApprovalOpen(false);
      setCancelApprovalForm({ email: "", password: "", reason: "" });
      setCart([]);
      setDiscountType("none");
      setSaleMessage("Current sale cancelled with supervisor approval.");
    } catch (error) {
      setCancelApprovalError(getApiErrorMessage(error, "Supervisor approval failed."));
    } finally {
      setCancelApprovalLoading(false);
    }
  };

  const handleReprintReceipt = async (sale) => {
    const opened = printReceipt(sale);

    if (!opened) {
      setPageError("Print window was blocked. Please allow pop-ups for this site and try again.");
      return;
    }

    await recordAudit("reprint_receipt", `Reprinted receipt for sale #${sale.id}.`);
    setSaleMessage(`Receipt for sale #${sale.id} opened for print or PDF saving.`);
  };

  const handleAdminProductSubmit = async (event) => {
    event.preventDefault();
    setPageError("");
    setSaleMessage("");
    setProductErrors({});

    const payload = {
      ...productForm,
      price: Number(productForm.price),
      stock: Number(productForm.stock),
    };

    try {
      if (editingProductId) {
        await updateProduct(editingProductId, payload);
        showProductToast("Product updated successfully");
      } else {
        await createProduct(payload);
        showProductToast("Product added successfully");
      }

      setProductForm(defaultProductForm);
      setProductErrors({});
      setEditingProductId(null);
      await Promise.all([refreshProducts(), refreshDashboard()]);
    } catch (error) {
      const validationErrors = error?.response?.data?.errors || {};

      setProductErrors(validationErrors);

      if (!Object.keys(validationErrors).length) {
        setPageError(getApiErrorMessage(error, "Failed to save product."));
      }
    }
  };

  const handleDeactivateProduct = async (product) => {
    try {
      const nextActive = !product.active;
      await updateProduct(product.id, { active: nextActive });
      setSaleMessage(`${product.name} was ${nextActive ? "reactivated" : "deactivated"}.`);
      await Promise.all([refreshProducts(), refreshDashboard()]);
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to update product status.");
    }
  };

  const handleAdminUserSubmit = async (event) => {
    event.preventDefault();
    setPageError("");
    setSaleMessage("");

    const payload = {
      ...userForm,
      role_id: Number(userForm.role_id),
      is_active: Boolean(userForm.is_active),
    };

    if (!payload.password) {
      delete payload.password;
    }

    try {
      if (editingUserId) {
        await updateUser(editingUserId, payload);
        setSaleMessage("User updated successfully.");
      } else {
        await createUser(payload);
        setSaleMessage("User created successfully.");
      }

      setUserForm(defaultUserForm);
      setEditingUserId(null);
      const [accounts, meta] = await Promise.all([getUsers(), getUserMeta(), refreshDashboard()]);
      setUsers(accounts);
      setRoles(meta.roles);
    } catch (error) {
      setPageError(error.response?.data?.message || "Failed to save user.");
    }
  };

  return (
    <div className="dashboard-shell">
      {productToast && (
        <div className="product-success-toast" role="status" aria-live="polite">
          <div className="product-success-check">
            <span>✓</span>
          </div>
          <div>
            <p className="product-success-title">Success</p>
            <p className="product-success-message">{productToast.message}</p>
          </div>
        </div>
      )}

      <aside className="dashboard-sidebar">
        <div>
          <p className="eyebrow">RetailPOS</p>
          <h2>Role-Based POS</h2>
          <p className="sidebar-copy">
            {isAdmin
              ? "Manage products, user accounts, and store activity."
              : isCashier
                ? "Handle sales quickly with barcode entry, discounts, and receipt reprint."
                : isSupervisor
                  ? "Monitor sales, stock alerts, and cashier activity in one place."
                  : "Manage daily store operations from one place."}
          </p>
        </div>

        <div className="operator-card">
          <span className="operator-avatar">{user.name.slice(0, 1)}</span>
          <div>
            <strong>{user.name}</strong>
            <p>{roleName}</p>
          </div>
        </div>

        <RoleTabs tabs={tabs} activeTab={activeTab} onChange={setActiveTab} />

        <button type="button" className="btn btn-secondary" onClick={onLogout}>
          Logout
        </button>
      </aside>

      <main className="dashboard-main">
        <header className="hero-panel">
          <div>
            <p className="eyebrow">POS Dashboard</p>
            <h1>Welcome back, {user.name}</h1>
            <p>
              {isAdmin
                ? "Manage products, accounts, and store activity from one place."
                : isCashier
                  ? "Ready to scan items, process sales, and keep the line moving."
                  : "Welcome back. Your dashboard is ready."}
            </p>
          </div>
        </header>

        <NoticeBanner tone="danger" message={pageError} />
        <NoticeBanner tone="success" message={saleMessage} />

        <section className="kpi-grid">
          <KpiCard
            label="Total Sales"
            value={dashboardLoading ? "Loading..." : peso(metrics.totalSales)}
            hint="Completed sales pulled from the backend"
            tone="amber"
          />
          <KpiCard
            label="Transactions"
            value={dashboardLoading ? "Loading..." : String(metrics.transactions)}
            hint="Recorded completed transactions"
            tone="blue"
          />
          <KpiCard
            label="Active Users"
            value={dashboardLoading ? "Loading..." : String(metrics.activeUsers)}
            hint="Unique login accounts with active status"
            tone="teal"
          />
          <KpiCard
            label="Low Stock Alerts"
            value={dashboardLoading ? "Loading..." : String(metrics.lowStockAlerts)}
            hint="Searchable products nearing stock-out"
            tone="rose"
          />
        </section>

        {isAdmin && activeTab === "products" && (
          <AdminProductsPanel
            products={products}
            categories={adminCategories}
            productForm={productForm}
            productErrors={productErrors}
            editingProductId={editingProductId}
            setProductForm={(updater) => {
              setProductForm((current) => {
                const nextValue = typeof updater === "function" ? updater(current) : updater;
                const changedKeys = Object.keys(nextValue).filter((key) => nextValue[key] !== current[key]);

                if (changedKeys.length) {
                  setProductErrors((currentErrors) => {
                    const nextErrors = { ...currentErrors };
                    changedKeys.forEach((key) => {
                      delete nextErrors[key];
                    });
                    return nextErrors;
                  });
                }

                return nextValue;
              });
            }}
            onSubmit={handleAdminProductSubmit}
            onEdit={(form, id) => {
              setProductForm(form);
              setProductErrors({});
              setEditingProductId(id);
            }}
            onCancelEdit={() => {
              setProductForm(defaultProductForm);
              setProductErrors({});
              setEditingProductId(null);
            }}
            onToggleActive={handleDeactivateProduct}
          />
        )}

        {isAdmin && activeTab === "users" && (
          <AdminUsersPanel
            users={users}
            roles={roles}
            userForm={userForm}
            editingUserId={editingUserId}
            setUserForm={setUserForm}
            onSubmit={handleAdminUserSubmit}
            onEdit={(form, id) => {
              setUserForm(form);
              setEditingUserId(id);
            }}
          />
        )}

        {isAdmin && activeTab === "overview" && (
          <section className="bottom-grid">
            <section className="panel">
              <div className="panel-heading">
                <div>
                  <p className="eyebrow">Alerts</p>
                  <h2>Low stock products</h2>
                </div>
              </div>
              {!dashboard?.lowStockProducts?.length ? (
                <div className="panel-empty">No low stock alerts right now.</div>
              ) : (
                <div className="list-stack">
                  {dashboard.lowStockProducts.map((product) => (
                    <div key={product.id} className="list-row">
                      <div>
                        <strong>{product.name}</strong>
                        <p>{product.barcode}</p>
                      </div>
                      <span className="stock-badge danger">{product.stock} left</span>
                    </div>
                  ))}
                </div>
              )}
            </section>

            <section className="panel">
              <div className="panel-heading">
                <div>
                  <p className="eyebrow">Transactions</p>
                  <h2>Recent sales</h2>
                </div>
              </div>
              {salesLoading ? (
                <div className="panel-empty">Loading transactions...</div>
              ) : (
                <div className="list-stack">
                  {sales.map((sale) => (
                    <div key={sale.id} className="list-row">
                      <div>
                        <strong>Sale #{sale.id}</strong>
                        <p>{sale.cashier?.name || "Unknown cashier"}</p>
                      </div>
                      <div className="sale-meta">
                        <strong>{peso(sale.total)}</strong>
                        <span>{sale.items.length} item(s)</span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </section>
          </section>
        )}

        {isCashier && activeTab === "sales" && (
          <section className="content-grid">
            <section className="panel sales-browser-panel">
              <div className="panel-heading">
                <div>
                  <p className="eyebrow">Cashier</p>
                  <h2>Process Sales Transactions</h2>
                </div>
              </div>

              <div className="toolbar-grid pos-toolbar">
                <label className="field">
                  <span>Search products</span>
                  <input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search by name, barcode, or category"
                  />
                </label>
              </div>

              <section className="scan-assist-panel compact-scan-panel">
                <div className="scan-assist-header">
                  <div>
                    <p className="eyebrow">Quick Pad</p>
                    <h3>Barcode Entry</h3>
                  </div>
                  <div className="scan-monitor">
                    <span className="scan-monitor-label">Scanner Monitor</span>
                    <div className="scan-preview inline-preview">{barcode || "Ready to scan"}</div>
                  </div>
                </div>
                <BarcodePad onPress={handleBarcodePadPress} />
              </section>

              <div className="sales-workspace categories-spaced">
                <ProductResults
                  products={filteredProducts}
                  loading={productsLoading}
                  onAdd={handleAddToCart}
                  categories={categories}
                  activeCategory={activeCategory}
                  onCategoryChange={setActiveCategory}
                />
              </div>
            </section>

            <CartPanel
              cart={cart}
              discountType={discountType}
              discountAmount={discountAmount}
              vatableSales={vatableSales}
              vatAmount={vatAmount}
              subtotal={subtotal}
              total={total}
              paying={paying}
              cancelApprovalOpen={cancelApprovalOpen}
              cancelApprovalForm={cancelApprovalForm}
              cancelApprovalError={cancelApprovalError}
              cancelApprovalLoading={cancelApprovalLoading}
              currentTime={currentTime}
              onCancelApprovalChange={setCancelApprovalForm}
              onCancelApprovalSubmit={handleCancelApprovalSubmit}
              onCancelApprovalClose={() => {
                setCancelApprovalOpen(false);
                setCancelApprovalError("");
              }}
              onDiscountChange={setDiscountType}
              onQuantityChange={handleQuantityChange}
              onRemove={(productId) => handleQuantityChange(productId, 0)}
              onCancel={handleCancelSale}
              onPay={handlePay}
            />
          </section>
        )}

        {isCashier && activeTab === "receipts" && (
          <CashierReceiptsPanel sales={sales} onReprint={handleReprintReceipt} />
        )}

        {isSupervisor && (
          <>
            {activeTab === "overview" && (
              <section className="bottom-grid">
                <section className="panel">
                  <div className="panel-heading">
                    <div>
                      <p className="eyebrow">Supervisor</p>
                      <h2>Store Overview</h2>
                    </div>
                  </div>
                  {!dashboard?.lowStockProducts?.length ? (
                    <div className="panel-empty">No low stock alerts right now.</div>
                  ) : (
                    <div className="list-stack">
                      {dashboard.lowStockProducts.map((product) => (
                        <div key={product.id} className="list-row">
                          <div>
                            <strong>{product.name}</strong>
                            <p>{product.barcode}</p>
                          </div>
                          <span className="stock-badge danger">{product.stock} left</span>
                        </div>
                      ))}
                    </div>
                  )}
                </section>

                <section className="panel">
                  <div className="panel-heading">
                    <div>
                      <p className="eyebrow">Supervisor</p>
                      <h2>Recent Sales</h2>
                    </div>
                  </div>
                  {salesLoading ? (
                    <div className="panel-empty">Loading transactions...</div>
                  ) : (
                    <div className="list-stack">
                      {sales.map((sale) => (
                        <div key={sale.id} className="list-row">
                          <div>
                            <strong>Sale #{sale.id}</strong>
                            <p>{sale.cashier?.name || "Unknown cashier"}</p>
                          </div>
                          <div className="sale-meta">
                            <strong>{peso(sale.total)}</strong>
                            <span>{sale.items.length} item(s)</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </section>
              </section>
            )}

            {activeTab === "activity" && (
              <section className="panel">
                <div className="panel-heading">
                  <div>
                    <p className="eyebrow">Supervisor</p>
                    <h2>Cashier Activity</h2>
                    <p className="panel-empty">Review voids, cancelled sales, and receipt reprints.</p>
                  </div>
                </div>

                {auditLoading ? (
                  <div className="panel-empty">Loading activity logs...</div>
                ) : !auditLogs.length ? (
                  <div className="panel-empty">No activity logs recorded yet.</div>
                ) : (
                  <div className="list-stack">
                    {auditLogs.map((log) => (
                      <div key={log.id} className="list-row activity-row">
                        <div>
                          <strong>{String(log.action).replaceAll("_", " ")}</strong>
                          <p>{log.details}</p>
                        </div>
                        <div className="sale-meta">
                          <strong>{log.user?.name || "Unknown user"}</strong>
                          <span>{new Date(log.logged_at).toLocaleString("en-PH")}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </section>
            )}

            {activeTab === "catalog" && (
              <section className="panel">
                <div className="panel-heading">
                  <div>
                    <p className="eyebrow">Supervisor</p>
                    <h2>Product Catalog</h2>
                    <p className="panel-empty">Read-only product view for stock and availability checks.</p>
                  </div>
                </div>

                {productsLoading ? (
                  <div className="panel-empty">Loading products...</div>
                ) : (
                  <div className="table-like">
                    {products.map((product) => (
                      <div key={product.id} className="table-row">
                        <div>
                          <strong>{product.name}</strong>
                          <p>{product.barcode} • {product.category}</p>
                        </div>
                        <div className="table-row-actions supervisor-catalog-actions">
                          <span className={`status-pill ${product.active ? "ok" : "off"}`}>
                            {product.active ? "Active" : "Inactive"}
                          </span>
                          <span>{product.stock} stock</span>
                          <span>PHP {Number(product.price).toFixed(2)}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </section>
            )}
          </>
        )}
      </main>
    </div>
  );
}

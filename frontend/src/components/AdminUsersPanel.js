export default function AdminUsersPanel({
  users,
  roles,
  userForm,
  editingUserId,
  setUserForm,
  onSubmit,
  onEdit,
}) {
  return (
    <section className="panel user-management-panel">
      <div className="panel-heading">
        <div>
          <p className="eyebrow">Administrator</p>
          <h2>Manage User Accounts and Roles</h2>
        </div>
      </div>

      <form className="admin-form-grid" onSubmit={onSubmit}>
        <label className="field">
          <span>Name</span>
          <input
            value={userForm.name}
            onChange={(event) => setUserForm((current) => ({ ...current, name: event.target.value }))}
          />
        </label>
        <label className="field">
          <span>Email</span>
          <input
            type="email"
            value={userForm.email}
            onChange={(event) => setUserForm((current) => ({ ...current, email: event.target.value }))}
          />
        </label>
        <label className="field">
          <span>Password</span>
          <input
            type="password"
            value={userForm.password}
            onChange={(event) => setUserForm((current) => ({ ...current, password: event.target.value }))}
            placeholder={editingUserId ? "Leave blank to keep current password" : "Minimum 8 characters"}
          />
        </label>
        <label className="field">
          <span>Role</span>
          <select
            value={userForm.role_id}
            onChange={(event) => setUserForm((current) => ({ ...current, role_id: event.target.value }))}
          >
            <option value="">Select a role</option>
            {roles.map((role) => (
              <option key={role.id} value={role.id}>
                {role.name}
              </option>
            ))}
          </select>
        </label>
        <label className="checkbox-field">
          <input
            type="checkbox"
            checked={userForm.is_active}
            onChange={(event) => setUserForm((current) => ({ ...current, is_active: event.target.checked }))}
          />
          <span>User is active</span>
        </label>
        <button type="submit" className="btn btn-primary">
          {editingUserId ? "Update User" : "Create User"}
        </button>
      </form>

      <div className="table-like">
        {users.map((account) => (
          <div key={account.id} className="table-row user-management-row">
            <div>
              <strong>{account.name}</strong>
              <p>{account.email}</p>
            </div>
            <div className="table-row-actions user-management-actions">
              <span className="user-role">{account.role?.name || "No role"}</span>
              <span className={`status-pill ${account.is_active ? "ok" : "off"}`}>
                {account.is_active ? "Active" : "Inactive"}
              </span>
              <span className="last-login">{account.last_login_at ? new Date(account.last_login_at).toLocaleString() : "No login yet"}</span>
              <button
                type="button"
                className="btn btn-secondary slim"
                onClick={() =>
                  onEdit({
                    name: account.name,
                    email: account.email,
                    password: "",
                    role_id: String(account.role_id || ""),
                    is_active: Boolean(account.is_active),
                  }, account.id)
                }
              >
                Edit
              </button>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}

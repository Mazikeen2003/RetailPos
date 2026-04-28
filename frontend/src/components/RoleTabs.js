export default function RoleTabs({ tabs, activeTab, onChange }) {
  return (
    <div className="role-tabs">
      {tabs.map((tab) => (
        <button
          key={tab.id}
          type="button"
          className={`role-tab ${activeTab === tab.id ? "active" : ""}`}
          onClick={() => onChange(tab.id)}
        >
          {tab.label}
        </button>
      ))}
    </div>
  );
}

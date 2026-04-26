export default function KpiCard({ label, value, hint, tone = "blue" }) {
  return (
    <article className={`kpi-card tone-${tone}`}>
      <p className="kpi-label">{label}</p>
      <strong className="kpi-value">{value}</strong>
      <p className="kpi-hint">{hint}</p>
    </article>
  );
}

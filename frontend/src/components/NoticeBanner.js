export default function NoticeBanner({ tone = "info", message }) {
  if (!message) {
    return null;
  }

  return <div className={`notice-banner tone-${tone}`}>{message}</div>;
}

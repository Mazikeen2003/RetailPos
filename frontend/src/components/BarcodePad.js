const padKeys = [
  { key: "1" },
  { key: "2" },
  { key: "3" },
  { key: "CLR", tone: "danger" },
  { key: "4" },
  { key: "5" },
  { key: "6" },
  { key: "DEL", tone: "neutral" },
  { key: "7" },
  { key: "8" },
  { key: "9" },
  { key: "ENTER", tone: "accent" },
  { key: "0", wide: true },
];

export default function BarcodePad({ onPress }) {
  return (
    <div className="barcode-pad">
      {padKeys.map((item) => (
        <button
          key={item.key}
          type="button"
          className={`barcode-key ${item.tone ? item.tone : ""} ${item.wide ? "wide" : ""}`}
          onClick={() => onPress(item.key)}
        >
          {item.key}
        </button>
      ))}
    </div>
  );
}

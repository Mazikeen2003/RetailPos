import React, { useEffect, useState, useRef } from 'react';

// Calculator-like QuickPad for barcode entry
export default function QuickPad({ compact = false, onSubmit, scannerActive = false, onToggleScanner } = {}) {
  const [value, setValue] = useState('');
  const [history, setHistory] = useState(() => {
    try {
      const raw = localStorage.getItem('rp_quickpad_history');
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  });
  const inputRef = useRef(null);

  useEffect(() => {
    if (scannerActive) {
      // focus input when scanner is activated
      setTimeout(() => inputRef.current?.focus(), 50);
    }
  }, [scannerActive]);

  useEffect(() => {
    try {
      localStorage.setItem('rp_quickpad_history', JSON.stringify(history));
    } catch (e) {}
  }, [history]);

  const append = (ch) => setValue(v => (v || '') + ch);
  const del = () => setValue(v => (v || '').slice(0, -1));
  const clr = () => setValue('');

  const handleEnter = () => {
    const code = (value || '').trim();
    if (!code) return;
    setHistory(h => [code, ...h].slice(0, 10));
    if (typeof onSubmit === 'function') {
      try { onSubmit(code); } catch (e) { /* ignore */ }
    } else {
      // fallback: copy to clipboard so user can paste
      try { navigator.clipboard?.writeText(code); } catch (e) {}
    }
    setValue('');
    inputRef.current?.focus();
  };

  const containerClasses = compact
    ? 'card-base p-3 border-2 dark:border-slate-700 h-full flex flex-col'
    : 'card-base p-4 md:p-6 border-2 dark:border-slate-700';

  const keypadAreaClass = compact ? 'flex-1 flex flex-col justify-between' : '';

  const titleClass = compact ? 'text-sm font-bold text-gray-900 dark:text-white' : 'text-lg md:text-xl font-bold text-gray-900 dark:text-white';
  const subtitleClass = 'text-xs text-gray-600 dark:text-gray-400';

  const keys = ['1','2','3','4','5','6','7','8','9'];

  return (
    <div className={containerClasses}>
      <div className="flex items-start justify-between mb-3">
        <div>
          <h3 className={titleClass}>{compact ? 'Quick Pad' : 'Barcode Entry'}</h3>
          <small className={subtitleClass}>{compact ? 'Quick keys' : 'Enter barcode using keypad'}</small>
        </div>
        <div className="text-right">
          <div className="text-xs text-gray-500 dark:text-gray-400">Scanner Monitor</div>
          <button
            type="button"
            onClick={() => typeof onToggleScanner === 'function' && onToggleScanner()}
            aria-pressed={scannerActive}
            className={`mt-1 px-3 py-1 rounded text-xs font-medium transition focus:outline-none ${scannerActive ? 'bg-green-600 text-white dark:bg-green-500' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'}`}
          >
            {scannerActive ? 'Scanning…' : 'Ready to scan'}
          </button>
        </div>
      </div>

      <div className={`${keypadAreaClass} mb-3`}> 
        <div className="mb-2">
          <input
            ref={inputRef}
            type="text"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && handleEnter()}
            placeholder="Scan or type barcode"
            className="input-base w-full text-lg text-right bg-white dark:bg-slate-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-slate-700"
          />
        </div>

        <div className="grid grid-cols-4 grid-rows-4 gap-2 items-stretch">
          {/* numbers 1-9 */}
          {keys.map((k, i) => {
            const row = Math.floor(i / 3) + 1;
            const col = (i % 3) + 1;
            return (
              <button
                key={k}
                onClick={() => append(k)}
                type="button"
                className={`rounded-xl w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm hover:shadow-md transition ${compact ? 'py-2 text-lg' : 'py-3 text-xl'}`}
                style={{ gridColumnStart: col, gridRowStart: row }}
              >
                {k}
              </button>
            );
          })}

          {/* CLR at col 4 row 1 */}
          <button
            onClick={clr}
            type="button"
            className={`rounded-xl w-full h-full flex items-center justify-center text-sm font-semibold bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-200 shadow-sm`}
            style={{ gridColumnStart: 4, gridRowStart: 1 }}
          >
            CLR
          </button>

          {/* DEL at col 4 row 2 */}
          <button
            onClick={del}
            type="button"
            className={`rounded-xl w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm`}
            style={{ gridColumnStart: 4, gridRowStart: 2 }}
          >
            DEL
          </button>

          {/* 0 spans full width of cols 1-3 on row 4 */}
          <button
            onClick={() => append('0')}
            type="button"
            className={`rounded-full col-span-3 w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm ${compact ? 'py-2 text-lg' : 'py-3 text-xl'}`}
            style={{ gridColumnStart: 1, gridRowStart: 4 }}
          >
            0
          </button>

          {/* ENTER at col 4 row 3 spanning 2 rows */}
          <button
            onClick={handleEnter}
            type="button"
            className={`rounded-xl w-full h-full flex items-center justify-center text-white font-semibold shadow-md bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500`}
            style={{ gridColumnStart: 4, gridRowStart: 3, gridRowEnd: 'span 2' }}
          >
            ENTER
          </button>
        </div>
      </div>

      {!compact && history.length > 0 && (
        <div className="mt-3 text-sm text-gray-600 dark:text-gray-400">
          <div className="font-semibold mb-1">Recent</div>
          <div className="space-y-1">
            {history.slice(0,5).map((h, idx) => (
              <div key={idx} className="flex items-center justify-between bg-white dark:bg-slate-800 rounded p-2 border border-gray-200 dark:border-slate-700">
                <div className="text-sm text-gray-800 dark:text-gray-100">{h}</div>
                <button className="text-xs text-blue-600 dark:text-blue-300" onClick={() => { setValue(h); inputRef.current?.focus(); }}>Use</button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

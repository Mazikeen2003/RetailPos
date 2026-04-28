import React, { useEffect, useRef, useState } from 'react';

// Calculator-like QuickPad for barcode or product search entry.
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
      setTimeout(() => inputRef.current?.focus(), 50);
    }
  }, [scannerActive]);

  useEffect(() => {
    try {
      localStorage.setItem('rp_quickpad_history', JSON.stringify(history));
    } catch (e) {}
  }, [history]);

  const append = (ch) => setValue((current) => `${current || ''}${ch}`);
  const del = () => setValue((current) => (current || '').slice(0, -1));
  const clr = () => setValue('');

  const handleEnter = () => {
    const query = (value || '').trim();

    if (!query) {
      return;
    }

    setHistory((current) => [query, ...current.filter((item) => item !== query)].slice(0, 10));

    if (typeof onSubmit === 'function') {
      onSubmit(query);
    } else {
      try {
        navigator.clipboard?.writeText(query);
      } catch (e) {}
    }

    setValue('');
    inputRef.current?.focus();
  };

  const containerClasses = compact
    ? 'card-base p-3 border-2 dark:border-slate-700 h-full flex flex-col'
    : 'card-base p-4 md:p-6 border-2 dark:border-slate-700';
  const keypadAreaClass = compact ? 'flex-1 flex flex-col justify-between' : '';
  const titleClass = compact
    ? 'text-sm font-bold text-gray-900 dark:text-white'
    : 'text-lg md:text-xl font-bold text-gray-900 dark:text-white';
  const subtitleClass = 'text-xs text-gray-600 dark:text-gray-400';
  const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];

  return (
    <div className={containerClasses}>
      <div className="flex items-start justify-between mb-3">
        <div>
          <h3 className={titleClass}>{compact ? 'Quick Pad' : 'Barcode Entry'}</h3>
          <small className={subtitleClass}>{compact ? 'Search or scan' : 'Enter barcode or product search'}</small>
        </div>
        <div className="text-right">
          <div className="text-xs text-gray-500 dark:text-gray-400">Scanner Monitor</div>
          {typeof onToggleScanner === 'function' ? (
            <button
              type="button"
              onClick={onToggleScanner}
              aria-pressed={scannerActive}
              className={`mt-1 px-3 py-1 rounded text-xs font-medium transition focus:outline-none ${scannerActive ? 'bg-green-600 text-white dark:bg-green-500' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'}`}
            >
              {scannerActive ? 'Scanning...' : 'Ready to scan'}
            </button>
          ) : (
            <span className="mt-1 inline-block px-3 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              Ready to scan
            </span>
          )}
        </div>
      </div>

      <div className={`${keypadAreaClass} mb-3`}>
        <div className="mb-2">
          <input
            ref={inputRef}
            type="text"
            value={value}
            onChange={(event) => setValue(event.target.value)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                handleEnter();
              }
            }}
            placeholder="Scan barcode or search products"
            className="input-base w-full text-lg text-right bg-white dark:bg-slate-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-slate-700"
          />
        </div>

        <div className="grid grid-cols-4 grid-rows-4 gap-2 items-stretch">
          {keys.map((key, index) => {
            const row = Math.floor(index / 3) + 1;
            const col = (index % 3) + 1;

            return (
              <button
                key={key}
                onClick={() => append(key)}
                type="button"
                className={`rounded-xl w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm hover:shadow-md transition ${compact ? 'py-2 text-lg' : 'py-3 text-xl'}`}
                style={{ gridColumnStart: col, gridRowStart: row }}
              >
                {key}
              </button>
            );
          })}

          <button
            onClick={clr}
            type="button"
            className="rounded-xl w-full h-full flex items-center justify-center text-sm font-semibold bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-200 shadow-sm"
            style={{ gridColumnStart: 4, gridRowStart: 1 }}
          >
            CLR
          </button>

          <button
            onClick={del}
            type="button"
            className="rounded-xl w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm"
            style={{ gridColumnStart: 4, gridRowStart: 2 }}
          >
            DEL
          </button>

          <button
            onClick={() => append('0')}
            type="button"
            className={`rounded-full col-span-3 w-full h-full flex items-center justify-center bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 shadow-sm ${compact ? 'py-2 text-lg' : 'py-3 text-xl'}`}
            style={{ gridColumnStart: 1, gridRowStart: 4 }}
          >
            0
          </button>

          <button
            onClick={handleEnter}
            type="button"
            className="rounded-xl w-full h-full flex items-center justify-center text-white font-semibold shadow-md bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500"
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
            {history.slice(0, 5).map((item) => (
              <div key={item} className="flex items-center justify-between bg-white dark:bg-slate-800 rounded p-2 border border-gray-200 dark:border-slate-700">
                <div className="text-sm text-gray-800 dark:text-gray-100">{item}</div>
                <button className="text-xs text-blue-600 dark:text-blue-300" onClick={() => { setValue(item); inputRef.current?.focus(); }}>
                  Use
                </button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

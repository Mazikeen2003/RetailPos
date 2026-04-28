import React, { useEffect, useState } from 'react';

export default function QuickPad() {
  const [notes, setNotes] = useState(() => {
    try {
      const raw = localStorage.getItem('rp_quickpad');
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  });
  const [text, setText] = useState('');

  useEffect(() => {
    try {
      localStorage.setItem('rp_quickpad', JSON.stringify(notes));
    } catch (e) {}
  }, [notes]);

  const addNote = () => {
    const t = text.trim();
    if (!t) return;
    setNotes(prev => [{ id: Date.now(), text: t }, ...prev]);
    setText('');
  };

  const deleteNote = (id) => setNotes(prev => prev.filter(n => n.id !== id));

  return (
    <div className="card-base p-4 md:p-6 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200">
      <div className="flex items-center justify-between mb-3">
        <h3 className="text-lg md:text-xl font-bold text-gray-900">QuickPad 📝</h3>
        <small className="text-xs text-gray-600">Retail quick notes</small>
      </div>

      <div className="flex gap-2 mb-3">
        <input
          type="text"
          value={text}
          onChange={(e) => setText(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && addNote()}
          placeholder="Add a quick note or reminder"
          className="input-base flex-1"
        />
        <button onClick={addNote} className="btn-primary btn-small">Add</button>
      </div>

      {notes.length === 0 ? (
        <p className="text-sm text-gray-600">No quick notes yet — add tips, promos, or reminders.</p>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {notes.map((n) => (
            <div key={n.id} className="p-3 rounded-lg bg-white shadow-sm border border-amber-100 flex justify-between items-start">
              <div className="text-sm text-gray-800 whitespace-pre-wrap">{n.text}</div>
              <button onClick={() => deleteNote(n.id)} className="text-red-500 ml-3 text-sm">✕</button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

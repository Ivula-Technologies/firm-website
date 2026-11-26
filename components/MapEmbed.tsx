"use client";

import { useState } from "react";
import { Icon } from "./Icon";

/** Google map that only loads (and only sets Google's cookies) after a click. */
export function MapEmbed({ query }: { query: string }) {
  const [on, setOn] = useState(false);
  return (
    <div className="map">
      {on ? (
        <iframe title="Map to our office" loading="lazy" referrerPolicy="no-referrer-when-downgrade" src={`https://www.google.com/maps?q=${encodeURIComponent(query)}&output=embed`} />
      ) : (
        <div className="map__ph">
          <Icon name="map-pin" size={28} />
          <p style={{ margin: 0 }}>{query}</p>
          <button type="button" className="btn btn--ghost btn--sm" onClick={() => setOn(true)}>Show map</button>
          <small>Loading the map lets Google set cookies.</small>
        </div>
      )}
    </div>
  );
}

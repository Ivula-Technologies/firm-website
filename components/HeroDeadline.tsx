"use client";

import { useEffect, useState } from "react";
import { upcomingDeadlines, type Deadline } from "@/lib/deadlines";

/** The hero's countdown card, recounted in the browser so a cached page stays right. */
export function HeroDeadline({ initial }: { initial: Deadline }) {
  const [next, setNext] = useState(initial);
  useEffect(() => {
    const d = upcomingDeadlines()[0];
    if (d) setNext(d);
  }, []);
  return (
    <div className="gcard gcard--deadline">
      <p className="gcard__k">Next KRA deadline</p>
      <p className="gcard__big">{next.days}<span>{next.days === 1 ? " day" : " days"}</span></p>
      <p className="gcard__s">{next.name}</p>
    </div>
  );
}

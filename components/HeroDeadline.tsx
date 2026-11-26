"use client";

import { useEffect, useState } from "react";
import { upcomingDeadlines, type Deadline } from "@/lib/deadlines";

/** The hero's next-deadline line, recounted in the browser so a cached page stays right. */
export function HeroDeadline({ initial }: { initial: Deadline }) {
  const [next, setNext] = useState(initial);
  useEffect(() => {
    const d = upcomingDeadlines()[0];
    if (d) setNext(d);
  }, []);
  return (
    <>
      <span className="vhero__k">Next KRA deadline</span>
      <span className="vhero__v">
        {next.name}, in {next.days} {next.days === 1 ? "day" : "days"}
      </span>
    </>
  );
}

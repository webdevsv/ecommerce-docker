export function GridSkeleton({ n = 8 }: { n?: number }) {
  return (
    <div className="grid grid-cols-2 gap-4 md:grid-cols-4" aria-busy="true" aria-label="Cargando">
      {Array.from({ length: n }, (_, i) => (
        <div key={i} className="animate-pulse rounded-lg border border-line bg-surface p-3">
          <div className="mb-3 aspect-square rounded bg-line" />
          <div className="mb-2 h-4 w-3/4 rounded bg-line" />
          <div className="h-4 w-1/3 rounded bg-line" />
        </div>
      ))}
    </div>
  );
}

export function ListSkeleton({ n = 3 }: { n?: number }) {
  return (
    <div className="space-y-3" aria-busy="true" aria-label="Cargando">
      {Array.from({ length: n }, (_, i) => <div key={i} className="h-24 animate-pulse rounded-lg bg-line" />)}
    </div>
  );
}

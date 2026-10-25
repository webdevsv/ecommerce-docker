import { NextRequest, NextResponse } from "next/server";

// Next 16: middleware.ts se renombro a proxy.ts
const PROTECTED = ["/orders", "/checkout"];

export function proxy(req: NextRequest) {
  const token = req.cookies.get("token")?.value;
  const isProtected = PROTECTED.some((p) => req.nextUrl.pathname.startsWith(p));

  if (isProtected && !token) {
    const url = new URL("/login", req.url);
    url.searchParams.set("next", req.nextUrl.pathname);
    return NextResponse.redirect(url);
  }
  return NextResponse.next();
}

export const config = { matcher: ["/orders/:path*", "/checkout/:path*"] };

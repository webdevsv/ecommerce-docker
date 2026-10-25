"use client";
import { useActionState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { loginAction, type FormState } from "@/lib/actions";

export default function LoginForm() {
  const [state, action, pending] = useActionState<FormState, FormData>(loginAction, {});
  const next = useSearchParams().get("next") ?? "";
  return (
    <form action={action} className="flex flex-col gap-3">
      <input type="hidden" name="next" value={next} />
      <div>
        <label htmlFor="email" className="mb-1 block text-sm font-medium">Correo</label>
        <input id="email" name="email" type="email" required autoComplete="email" defaultValue={state.values?.email} className="input" />
        {state.fieldErrors?.email && <p className="field-error">{state.fieldErrors.email[0]}</p>}
      </div>
      <div>
        <label htmlFor="password" className="mb-1 block text-sm font-medium">Contraseña</label>
        <input id="password" name="password" type="password" required autoComplete="current-password" className="input" />
        {state.fieldErrors?.password && <p className="field-error">{state.fieldErrors.password[0]}</p>}
      </div>
      {state.error && <p role="alert" className="field-error">{state.error}</p>}
      <button disabled={pending} className="btn">{pending ? "Ingresando..." : "Ingresar"}</button>
      <p className="text-sm text-muted">¿No tienes cuenta? <Link href="/register" className="text-brand underline">Crea una</Link></p>
    </form>
  );
}

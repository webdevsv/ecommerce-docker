"use client";
import { useActionState } from "react";
import Link from "next/link";
import { registerAction, type FormState } from "@/lib/actions";

const fields = [
  { name: "name", type: "text", label: "Nombre", required: true, auto: "name" },
  { name: "email", type: "email", label: "Correo", required: true, auto: "email" },
  { name: "password", type: "password", label: "Contraseña (mínimo 8 caracteres)", required: true, auto: "new-password" },
  { name: "password_confirmation", type: "password", label: "Confirmar contraseña", required: true, auto: "new-password" },
  { name: "phone", type: "tel", label: "Teléfono (opcional)", required: false, auto: "tel" },
  { name: "address", type: "text", label: "Dirección (opcional)", required: false, auto: "street-address" },
];

export default function RegisterForm() {
  const [state, action, pending] = useActionState<FormState, FormData>(registerAction, {});
  return (
    <form action={action} className="flex flex-col gap-3">
      {fields.map((f) => (
        <div key={f.name}>
          <label htmlFor={f.name} className="mb-1 block text-sm font-medium">{f.label}</label>
          <input
            id={f.name} name={f.name} type={f.type} required={f.required} autoComplete={f.auto}
            defaultValue={f.type === "password" ? undefined : state.values?.[f.name]}
            className="input"
          />
          {state.fieldErrors?.[f.name] && <p className="field-error">{state.fieldErrors[f.name][0]}</p>}
        </div>
      ))}
      {state.error && <p role="alert" className="field-error">{state.error}</p>}
      <button disabled={pending} className="btn">{pending ? "Creando cuenta..." : "Crear cuenta"}</button>
      <p className="text-sm text-muted">¿Ya tienes cuenta? <Link href="/login" className="text-brand underline">Ingresa</Link></p>
    </form>
  );
}

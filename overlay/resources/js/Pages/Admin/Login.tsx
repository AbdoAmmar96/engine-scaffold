import { useForm } from "@inertiajs/react";
import { Button, Field, Input } from "@/Components/admin/ui";

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
        remember: true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post("/admin/login");
    };

    return (
        <div dir="rtl" className="flex min-h-screen items-center justify-center bg-bg-dark p-4 font-sans">
            <div className="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl">
                <div className="mb-8 flex flex-col items-center gap-3">
                    <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-lg font-extrabold text-primary-fg">
                        BP
                    </span>
                    <h1 className="text-lg font-extrabold text-gray-900">لوحة تحكم الإنجن</h1>
                </div>

                <form onSubmit={submit} className="flex flex-col gap-4">
                    <Field label="البريد الإلكتروني" error={errors.email}>
                        <Input
                            type="email"
                            dir="ltr"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            autoFocus
                        />
                    </Field>

                    <Field label="كلمة المرور" error={errors.password}>
                        <Input type="password" dir="ltr" value={data.password} onChange={(e) => setData("password", e.target.value)} />
                    </Field>

                    <Button type="submit" disabled={processing} className="mt-2 w-full">
                        {processing ? "جارٍ الدخول…" : "دخول"}
                    </Button>
                </form>
            </div>
        </div>
    );
}

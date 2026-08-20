import { Link, usePage } from "@inertiajs/react";
import { HeartOff } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import PropertyCard from "@/Components/site/PropertyCard";
import type { Property, SharedProps } from "@/lib/types";

export default function Favorites({ properties }: { properties: Property[] }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const wa = settings.contact?.whatsapp;

    return (
        <AccountLayout title={ar ? "العقارات المحفوظة" : "Saved properties"}>
            {properties.length > 0 ? (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {properties.map((p) => (
                        <PropertyCard key={p.id} p={p} ar={ar} wa={wa} />
                    ))}
                </div>
            ) : (
                <div className="flex flex-col items-center rounded-2xl border border-gray-100 bg-bg px-6 py-16 text-center">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <HeartOff size={24} />
                    </span>
                    <p className="mt-4 text-sm text-muted">
                        {ar
                            ? "مفيش عقارات محفوظة لسه — اضغط على القلب في أي وحدة عجباك."
                            : "No saved properties yet — tap the heart on any unit you like."}
                    </p>
                    <Link
                        href={`/${locale}/properties`}
                        className="mt-6 rounded-brand bg-primary px-6 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                    >
                        {ar ? "تصفّح العقارات" : "Browse properties"}
                    </Link>
                </div>
            )}
        </AccountLayout>
    );
}

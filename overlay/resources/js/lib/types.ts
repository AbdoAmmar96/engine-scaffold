export type SettingsGroups = Record<string, Record<string, string>>;

export interface AuthUser {
    id: number;
    name: string;
    email: string;
}

export interface SharedProps {
    settings: SettingsGroups;
    locale: "ar" | "en";
    auth: { user: AuthUser | null };
    flash: { success?: string | null; error?: string | null };
    [key: string]: unknown;
}

export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number | null;
    to: number | null;
}

export interface Property {
    id: number;
    title: string;
    area: string;
    purpose: string;
    price: string;
    beds: number;
    baths: number;
    size: number;
    ref: string;
}

export interface Compound {
    id: number;
    name: string;
    developer: string;
    area: string;
    starting: string;
    down: string;
    years: string;
    new: boolean;
}

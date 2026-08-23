import { Card } from "@/Components/admin/ui";
import { MediaGrid } from "@/Components/admin/MediaPicker";
import AdminLayout from "@/Layouts/AdminLayout";

export default function MediaIndex() {
    return (
        <AdminLayout title="مكتبة الميديا">
            <Card title="الملفات">
                <p className="mb-4 text-sm text-gray-500">
                    الملفات هنا تظهر في كل حقول الصور والفيديو في اللوحة — اضغط «اختر من المكتبة» بجانب أي حقل.
                </p>

                <MediaGrid />
            </Card>
        </AdminLayout>
    );
}

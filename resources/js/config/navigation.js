import {
    LayoutDashboard, PackagePlus, Boxes, ReceiptText, ShoppingBag, Truck,
    Heart, FileText, Bot, MapPin, ShoppingCart, ClipboardList,
} from 'lucide-vue-next';

export const pharmacistNavGroups = [
    {
        title: 'MAIN MENU',
        items: [
            { label: 'Dashboard', href: '/', icon: LayoutDashboard },
            { label: 'Products', href: '/medicines', icon: PackagePlus },
            { label: 'Inventory', href: '/inventory', icon: Boxes },
        ],
    },
    {
        title: 'OPERATIONS',
        items: [
            { label: 'Billing', href: '/billing', icon: ReceiptText },
            { label: 'Sales', href: '/sales', icon: ShoppingBag },
            { label: 'Suppliers', href: '/suppliers', icon: Truck },
        ],
    },
];

export const supplierNavGroups = [
    {
        title: 'MAIN MENU',
        items: [
            { label: 'Dashboard', href: '/', icon: LayoutDashboard },
            { label: 'Incoming Orders', href: '/supplier/orders', icon: Truck },
            { label: 'My Inventory', href: '/supplier/inventory', icon: Boxes },
        ],
    },
];

export const customerNavGroups = [
    {
        title: 'SHOP',
        items: [
            { label: 'Dashboard', href: '/shop/dashboard', icon: LayoutDashboard },
            { label: 'Medicines', href: '/shop/medicines', icon: PackagePlus },
            { label: 'Cart', href: '/shop/cart', icon: ShoppingCart },
            { label: 'Wishlist', href: '/shop/wishlist', icon: Heart },
        ],
    },
    {
        title: 'ORDERS',
        items: [
            { label: 'My Orders', href: '/shop/orders', icon: ClipboardList },
            { label: 'Prescriptions', href: '/shop/prescriptions', icon: FileText },
            { label: 'AI Consultant', href: '/shop/consultant', icon: Bot },
        ],
    },
    {
        title: 'MORE',
        items: [
            { label: 'Emergency', href: '/shop/emergency', icon: MapPin },
            { label: 'Browse Store', href: '/shop', icon: ShoppingBag },
        ],
    },
];

/** Resolve medicine image URL — SVG assets in /public/images/medicines */
const SLUG_MAP = {
    paracetamol: 'paracetamol',
    ibuprofen: 'ibuprofen',
    amoxicillin: 'amoxicillin',
    azithromycin: 'azithromycin',
    cetirizine: 'cetirizine',
    vitamin: 'vitamin',
    metformin: 'metformin',
    omeprazole: 'omeprazole',
    dolo: 'dolo',
    combiflam: 'combiflam',
    montelukast: 'montelukast',
    crocin: 'paracetamol',
    brufen: 'ibuprofen',
};

export function medicineImageSlug(name = '', brand = '') {
    const text = `${name} ${brand}`.toLowerCase();
    for (const [key, slug] of Object.entries(SLUG_MAP)) {
        if (text.includes(key)) return slug;
    }
    return 'default';
}

export function medicineImageUrl(name = '', brand = '', existing = null) {
    if (existing && existing.startsWith('/images/medicines/') && existing.endsWith('.svg')) {
        return existing;
    }
    const slug = medicineImageSlug(name, brand);
    return `/images/medicines/${slug}.svg`;
}

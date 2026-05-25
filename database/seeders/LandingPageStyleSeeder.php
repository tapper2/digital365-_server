<?php

namespace Database\Seeders;

use App\Models\LandingPageStyle;
use Illuminate\Database\Seeder;

class LandingPageStyleSeeder extends Seeder
{
    public function run(): void
    {
        LandingPageStyle::truncate();

        $styles = [
            [
                'name_he'        => 'מודרני נקי ובהיר',
                'name_en'        => 'Modern Clean',
                'tags_he'        => 'לבן, מינימליסטי, Apple style, whitespace',
                'suitable_for_he'=> 'עורכי דין, יועצים, SaaS, פיננסים',
                'image_prompt'   => 'Pure white minimalist professional photography. Crisp white studio background with soft directional shadows. Apple-inspired clean aesthetic, generous whitespace, ultra-clean lines. Person on right side in front of bright clean white background. Left panel: pure white background with subtle warm gray tones for Hebrew text layout.',
                'sort_order'     => 1,
            ],
            [
                'name_he'        => 'עסקי מודרני',
                'name_en'        => 'Corporate Modern',
                'tags_he'        => 'כחול כהה, corporate, גורדי שחקים, אמינות',
                'suitable_for_he'=> 'פיננסים, נדל״ן, ביטוח, משרדים',
                'image_prompt'   => 'Dark navy blue corporate professional photography. Modern glass office skyscrapers backdrop at dramatic blue-hour twilight, city lights glowing below. Serious trust-inspiring corporate atmosphere. Person on right side. Left panel: deep navy #0a1628 background with strong corporate energy for Hebrew text.',
                'sort_order'     => 2,
            ],
            [
                'name_he'        => 'קלאסי יוקרתי',
                'name_en'        => 'Classic Luxury',
                'tags_he'        => 'שחור וזהב, ספרייה, עץ כהה, luxury',
                'suitable_for_he'=> 'עורכי דין, נדל״ן יוקרה, השקעות',
                'image_prompt'   => 'Black and gold classic luxury professional photography. Rich dark mahogany wood paneling or leather-bound library study setting in background. Timeless old-money elegance and prestige. Person on right side. Left panel: deep matte black #0d0d0d with warm gold accent border elements for Hebrew text.',
                'sort_order'     => 3,
            ],
            [
                'name_he'        => 'אינסטגרמי צבעוני',
                'name_en'        => 'Instagram Style',
                'tags_he'        => 'gradients, צבעים בולטים, CTA חזק, social media',
                'suitable_for_he'=> 'פרסום, קמפיינים, מודעות פייסבוק',
                'image_prompt'   => 'Vibrant bold Instagram advertisement photography. Vivid purple-to-magenta or coral-to-orange gradient background, eye-catching energetic social media vibe with strong visual impact. Person on right side. Left panel: bold gradient continuation with strong CTA energy for Hebrew text.',
                'sort_order'     => 4,
            ],
            [
                'name_he'        => 'פייסבוק מודרני',
                'name_en'        => 'Facebook Ad Modern',
                'tags_he'        => 'conversion, clean CTA, שיווקי, mobile first',
                'suitable_for_he'=> 'לידים, דפי נחיתה, קמפיינים',
                'image_prompt'   => 'Clean modern Facebook ad conversion photography. Bright professional neutral studio setting, approachable and friendly energy, strong visual hierarchy for lead generation. Person on right side. Left panel: clean white or light gray background optimized for conversion Hebrew text.',
                'sort_order'     => 5,
            ],
            [
                'name_he'        => 'סטארטאפ הייטק',
                'name_en'        => 'Startup Tech',
                'tags_he'        => 'AI vibe, glassmorphism, gradients, חדשני',
                'suitable_for_he'=> 'AI, Cyber, SaaS, טכנולוגיה',
                'image_prompt'   => 'Futuristic dark startup tech photography with glassmorphism aesthetic. Deep charcoal background with subtle glowing blue and purple frosted glass panels, soft neon edge lighting, abstract tech geometry. Person on right side. Left panel: dark glassmorphism frosted panel with tech glow for Hebrew text.',
                'sort_order'     => 6,
            ],
            [
                'name_he'        => 'מגזין יוקרתי',
                'name_en'        => 'Editorial Magazine',
                'tags_he'        => 'טיפוגרפיה ענקית, fashion style, crop אומנותי',
                'suitable_for_he'=> 'מותגים אישיים, עורכי דין, יועצים',
                'image_prompt'   => 'High-fashion editorial magazine photography. Dramatic moody cinematic lighting, fashion-forward artistic composition, bold editorial luxury aesthetic. Person on right side. Left panel: editorial near-black dark background for oversized luxury Hebrew typographic layout.',
                'sort_order'     => 7,
            ],
            [
                'name_he'        => 'מינימליסטי סקנדינבי',
                'name_en'        => 'Scandinavian Minimal',
                'tags_he'        => 'צבעים רכים, clean, soft shadows',
                'suitable_for_he'=> 'אדריכלות, פרימיום, עיצוב פנים',
                'image_prompt'   => 'Soft Scandinavian minimalist professional photography. Pale warm cream or sand background, light natural oak wood texture accents, gentle diffused soft shadows. Premium clean Nordic design aesthetic. Person on right side. Left panel: warm off-white #f4efe6 background for clean Hebrew text.',
                'sort_order'     => 8,
            ],
            [
                'name_he'        => 'נדל״ן יוקרתי',
                'name_en'        => 'Luxury Real Estate',
                'tags_he'        => 'פנטהאוזים, sunset, marble, luxury skyline',
                'suitable_for_he'=> 'נדל״ן, פינוי בינוי, השקעות',
                'image_prompt'   => 'Premium luxury real estate professional photography. Marble penthouse lobby interior or floor-to-ceiling glass walls with spectacular golden hour sunset panoramic skyline view. Aspirational luxury atmosphere. Person on right side. Left panel: off-white marble or warm luxury cream tones for Hebrew text.',
                'sort_order'     => 9,
            ],
            [
                'name_he'        => 'עתידני טכנולוגי',
                'name_en'        => 'Futuristic AI',
                'tags_he'        => 'neon, dark tech, futuristic gradients',
                'suitable_for_he'=> 'AI, Cyber, חברות טכנולוגיה',
                'image_prompt'   => 'Dark cyberpunk futuristic AI professional photography. Near-black background with neon cyan and electric blue glowing accent elements, subtle circuit board patterns, holographic depth. Person on right side. Left panel: near-black #050a14 with neon blue accent glow for Hebrew text.',
                'sort_order'     => 10,
            ],
            [
                'name_he'        => 'חם ואנושי',
                'name_en'        => 'Human Warm',
                'tags_he'        => 'תאורה חמה, משרד ביתי, vibe אישי',
                'suitable_for_he'=> 'יועצים, מאמנים, עצמאים',
                'image_prompt'   => 'Warm personal lifestyle professional photography. Cozy home office setting with warm amber afternoon sunlight streaming in, natural wood tones, bookshelf backdrop, personal and approachable feel. Person on right side. Left panel: warm cream #f7f0e3 background for friendly Hebrew text.',
                'sort_order'     => 11,
            ],
            [
                'name_he'        => 'נשי אלגנטי',
                'name_en'        => 'Feminine Elegant',
                'tags_he'        => 'ורוד בהיר, nude, soft luxury',
                'suitable_for_he'=> 'יועצות, קוסמטיקה, אופנה, בריאות',
                'image_prompt'   => 'Soft elegant feminine luxury professional photography. Blush pink dusty rose and nude tones, delicate soft florals or abstract watercolor texture in background. Refined premium feminine aesthetic. Person on right side. Left panel: soft blush #fdf4f4 background for elegant Hebrew text.',
                'sort_order'     => 12,
            ],
            [
                'name_he'        => 'צעיר ודינמי',
                'name_en'        => 'Young Dynamic',
                'tags_he'        => 'bold colors, modern typography, energetic',
                'suitable_for_he'=> 'סטארטאפים, אפליקציות, מותגים צעירים',
                'image_prompt'   => 'Energetic bold youthful professional photography. Vibrant saturated colors, dynamic high-contrast composition, modern Gen-Z aesthetic with strong visual energy. Person on right side. Left panel: bold vivid color swatch with high-energy vibe for Hebrew text.',
                'sort_order'     => 13,
            ],
            [
                'name_he'        => 'פרימיום בינלאומי',
                'name_en'        => 'International Premium',
                'tags_he'        => 'כמו חברות ענק, clean corporate, ultra polished',
                'suitable_for_he'=> 'תאגידים, ייעוץ בכיר, שירותים פרימיום',
                'image_prompt'   => 'Ultra-polished international corporate premium professional photography. Fortune-500 company visual standard, immaculate clean studio setting, premium everywhere. Understated sophistication. Person on right side. Left panel: sophisticated cool-gray or pristine white for premium Hebrew text.',
                'sort_order'     => 14,
            ],
            [
                'name_he'        => 'דרמטי קולנועי',
                'name_en'        => 'Cinematic Dramatic',
                'tags_he'        => 'shadows, cinematic lighting, dramatic composition',
                'suitable_for_he'=> 'מותגים אישיים חזקים, נדל״ן, יועצים',
                'image_prompt'   => 'Cinematic dramatic chiaroscuro professional photography. Deep film-noir shadows with harsh spotlight accents, atmospheric depth, Hollywood-quality dramatic composition and lighting. Person on right side. Left panel: near-black cinematic shadow for powerful Hebrew text.',
                'sort_order'     => 15,
            ],
        ];

        foreach ($styles as $style) {
            LandingPageStyle::create($style);
        }
    }
}

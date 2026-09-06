<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Post;
use App\Models\Option;
use App\Models\Speech;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebsiteDefaultsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedOptions();
            $this->seedSlider();
            $this->seedNotices();
            $this->seedGalleries();
            $this->seedSpeeches();
        });

        $this->command?->info('Website defaults seeded successfully.');
    }

    private function seedOptions(): void
    {
        $staticLogo = [
            'url' => asset('images/default-logo.png'),
            'path' => 'images/default-logo.png',
            'provider' => 'static',
        ];

        $options = [
            'site.visitor_count' => 1000,
            'institute.branding.name' => 'New School Name',
            'institute.branding.show_top_header' => 1,
            'institute.branding.logo_json' => $staticLogo,
            'institute.theme.about' => 'with-notice',
            'institute.theme.slider_type' => 'slider_only',
            'institute.theme.banner_type' => 'info_only',
            'institute.identity.established_year' => 2001,
            'institute.identity.eiin' => '123456',
            'institute.identity.code' => 'BMS-001',
            'institute.contact.address' => 'Sylhet, Bangladesh',
            'institute.contact.phone' => '+8801700000000',
            'institute.contact.email' => 'info@barnomala.com',
            'institute.contact.website' => 'https://example.com',
            'institute.contact.map_link' => 'https://maps.google.com/?q=23.8103,90.4125',
            'institute.footer.text' => '',
            'institute.social.facebook' => '#',
            'institute.social.youtube' => '#',
            'speech.row.1.config' => '2 items',
            'institute.about.title' => 'আমাদের সম্পর্কে',
            'institute.about.button_text' => 'Read More',
            'institute.about.text' => 'আমাদের শিক্ষা প্রতিষ্ঠান একটি ঐতিহ্যবাহী বিদ্যাপীঠ। দীর্ঘ পথচলায় আমরা অসংখ্য মেধাবী শিক্ষার্থী উপহার দিয়েছি যারা দেশ ও দশের কল্যাণে নিয়োজিত। আমাদের লক্ষ্য হলো শিক্ষার্থীদের সুপ্ত প্রতিভা বিকাশে সহায়তা করা এবং তাদের সুনাগরিক হিসেবে গড়ে তোলা।',
            'institute.about.image_json' => [
                'url' => asset('images/about-image.webp'),
                'path' => 'images/about-image.webp',
                'provider' => 'static',
            ],
            'institute.homepage.layout' => [
                'hero' => true,
                'latest_news' => true,
                'institute_info' => true,
                'speech_section' => true,
                'stats_counter' => true,
                'quick_links' => true,
                'teachers' => false,
                'student_demographics' => true,
                'featured_news' => false,
                'gallery' => true,
                'general_committee' => false,
            ],
            'transfer.locks.json' => [
                'speeches' => false,
                'sliders' => false,
                'galleries' => false,
                'notices' => false,
                'news' => false,
            ],
            'institute.links.important_json' => [
                ['title' => 'Sylhet Education Board', 'url' => 'https://www.sylhetboard.gov.bd'],
                ['title' => 'Directorate of Secondary and Higher Education', 'url' => 'https://www.dshe.gov.bd'],
                ['title' => 'Ministry of Education', 'url' => 'https://moedu.gov.bd'],
                ['title' => 'Bangladesh National Portal', 'url' => 'https://bangladesh.gov.bd'],
                ['title' => 'BANBEIS', 'url' => 'https://www.banbeis.gov.bd'],
                ['title' => 'Teachers Portal', 'url' => 'https://www.teachers.gov.bd'],
            ],
            'institute.stats.classes_count' => 7,
            'institute.stats.students_count' => 1200,
            'institute.stats.teachers_count' => 20,
            'institute.stats.staffs_count' => 4,
            'institute.demographics.classes' => [
                'Six' => 120,
                'Seven' => 110,
                'Eight' => 105,
                'Nine' => 95,
                'Ten' => 80,
            ],
            'institute.demographics.gender' => [
                'Male' => 260,
                'Female' => 250,
                'Other' => 0,
            ],
            'institute.demographics.religion' => [
                'Islam' => 430,
                'Hindu' => 80,
                'Christian' => 0,
                'Buddhism' => 0,
            ],
            'institute.branches.json' => [
                [
                    'name' => 'Main Branch',
                    'sub_domain' => 'www',
                    'root_domain' => '',
                    'school_id' => '',
                ],
            ],
        ];

        foreach ($options as $key => $value) {
            Option::set($key, $value);
        }
    }

    private function seedSlider(): void
    {
        Option::set('institute.branding.slider_json', [
            [
                'url' => asset('images/default-slider.jpeg'),
                'path' => 'images/default-slider.jpeg',
                'title' => '',
                'order' => 1,
            ],
        ]);
    }

    private function seedNotices(): void
    {
        $samples = [
            [
                'title' => 'নতুন শিক্ষাবর্ষের ইউনিফর্ম ও পাঠ্যপুস্তক বিতরণ',
                'content' => 'নতুন শিক্ষাবর্ষের সকল শিক্ষার্থীদের মধ্যে বিনামূল্যে পাঠ্যপুস্তক বিতরণ করা হবে। ১লা জানুয়ারি বই উৎসব পালন করা হবে।',
                'published_at' => now()->subDay()->toDateString(),
                'is_active' => true,
                'is_urgent' => false,
            ],
        ];

        foreach ($samples as $sample) {
            Post::query()->updateOrCreate(
                ['source_type' => Post::NOTICE, 'title' => $sample['title']],
                $sample + [
                    'type' => Post::NOTICE,
                    'source_type' => Post::NOTICE,
                ]
            );
        }
    }

    private function seedGalleries(): void
    {
        $samples = [
            [
                'legacy_id' => 900001,
                'type' => Gallery::TYPE_PHOTO,
                'title' => 'Sample Gallery 1',
                'category' => 'sample',
                'date' => now()->subDays(7)->toDateString(),
                'image_path' => 'images/picture-1.jpg',
                'description' => 'Sample gallery photo 1 for initial setup.',
            ],
            [
                'legacy_id' => 900002,
                'type' => Gallery::TYPE_PHOTO,
                'title' => 'Sample Gallery 2',
                'category' => 'sample',
                'date' => now()->subDays(6)->toDateString(),
                'image_path' => 'images/picture-2.jpg',
                'description' => 'Sample gallery photo 2 for initial setup.',
            ],
        ];

        foreach ($samples as $sample) {
            Gallery::query()->updateOrCreate(
                ['legacy_id' => $sample['legacy_id']],
                $sample
            );
        }
    }

    private function seedSpeeches(): void
    {
        $samples = [
            [
                'name' => 'প্রধান শিক্ষক',
                'title' => 'প্রধান শিক্ষকের বাণী',
                'speech' => 'সরকারি মডেল হাইস্কুল এন্ড কলেজ শিক্ষার অগ্রগতিতে অনন্য ভূমিকা রাখছে। শিক্ষার পাশাপাশি অত্র প্রতিষ্ঠানের শিক্ষার্থীরা সাহিত্য, সংস্কৃতি, খেলাধুলা, প্রযুক্তি, বিতর্ক, বিজ্ঞান সকল ক্ষেত্রে কৃতিত্ব অর্জন করে বিদ্যালয়ের সুনাম অক্ষুন্ন রেখে চলেছে। অত্র বিদ্যালয় থেকে একজন শিক্ষার্থী মেধাবী, সৎ, কর্মঠ, আধুনিক বিজ্ঞান মনষ্ক, যোগ্য দেশপ্রেমিক ও মানবিক মূল্যবোধ সম্পন্ন আলোকিত মানুষ হয়ে বাংলাদেশকে সমৃদ্ধের পথে এগিয়ে নিয়ে যাবে এ আমার দৃঢ় প্রত্যাশা। বিদ্যালয় দৃঢ়ভাবে বিশ্বাস করে সন্তানের শিক্ষার জন্য অভিভাবকের পরিকল্পিত বিনিয়োগই সর্বশ্রেষ্ঠ বিনিয়োগ। সন্তানের সু-শিক্ষার জন্য সমাজ গুরুত্বপূর্ণ প্লাটফরম। সন্তানকে সময় দিন, সে যাতে সমাজকে বুঝতে পারে। সন্তানের সাথে মাঝে মাঝে বিভিন্ন ধরনের বিনোদনে অংশ নিন। সমাজের অসঙ্গগতিগুলো নিয়ে কথা বলুন। তার নিজের দেশের এবং পৃথিবীর সমস্যাগুলো নিয়ে তার সাথে আলোচনা করুন। আলোচনা করুন সমাজে ও পরিবারে তার দায়িত্ব নিয়ে। একজন শিক্ষার্থী বিদ্যালয়ের নিয়ন্ত্রনে যতক্ষণ থাকে পরিবারের নিয়ন্ত্রনে থাকে তার চেয়ে তিনগুন সময়। এই সময়টা সে কিভাবে ব্যবহার করছে তার উপরই নির্ভর করে তার সামগ্রিক বিকাশ। যেকোন পরীক্ষায় জি.পি.এ পাঁচ (৫.০০) প্রাপ্তিই সর্বোচ্চ প্রাপ্তি নয়, শিক্ষার্থীর শারীরিক, মানসিক এবং আবেগিক বিকাশ জরুরী। বিংশ শতাব্দীর চ্যালেঞ্জ মোকাবেলা করে একটি ডিজিটাল এবং সুন্দর বাংলাদেশ নির্মানের যে স্বপ্ন আমরা দেখছি তা বাস্তবায়নে পাঠ্যপুস্তকের জ্ঞান অর্জনের সাথে সাথে কো-কারিকুলাম সংক্রান্ত কাজও গুরুত্ব সহকারে আয়ত্ব করতে হবে। আসুন বিদ্যালয় এবং অভিভাবকবৃন্দের পরিকল্পিত প্রচেষ্টার মাধ্যমে আমরা আগামী প্রজন্মকে গড়ে তুলতে অংশ গ্রহণ করি এবং নির্মান করি সমৃদ্ধশালী বাংলাদেশ এবং বাসযোগ্য পৃথিবী। পরম করুনাময়ের কাছে প্রার্থনা সকলের মিলিত প্রয়াসে আমাদের প্রিয় শিক্ষাঙ্গনে শান্তি-শৃঙ্খলা ও শিক্ষার সুষ্ঠু পরিবেশ বজায় রেখে সকলে যেন অভীষ্ঠ লক্ষ্যে পৌঁছাতে পারি।',
                'image_json' => [
                    'url' => asset('images/teacher.png'),
                    'path' => 'images/teacher.png',
                    'provider' => 'static',
                ],
                'row_index' => 1,
                'column_index' => 1,
                'colspan' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'সভাপতি',
                'title' => 'সভাপতির বাণী',
                'speech' => 'আমাদের ওয়েবসাইট প্রস্তুত হচ্ছে  জেনে আমি খুবই আনন্দিত। এর মাধ্যমে প্রতিষ্ঠান পরিচিতি ও সার্বিক কার্যক্রমে গতিশীলতা ও জবাবদিহিতা নিশ্চিত হবে বলে আমি মনে করি। আশা করি, ওয়েবসাইট ডেভেলপমেন্ট কার্যক্রমটি তথ্যবহুল হবে এবং আপডেট থাকবে। ওয়েবসাইট প্রস্তুতকরণের সাথে সংশ্লিষ্ট সবাইকে আমার আন্তরিক ধন্যবাদ ও অভিনন্দন। এ প্রতিষ্ঠানের শিক্ষার্থীরা সঠিক জ্ঞান অর্জনের মাধ্যমে ভবিষ্যতে আলোকিত মানুষ হয়ে দেশ ও জনগণের সেবক হিসেবে গড়ে উঠুক এবং তাদের পথ চলা হোক সত্য, সুন্দর, কল্যাণ ও আলোর পথে। সবার জন্য আমার শুভ কামনা।',
                'image_json' => [
                    'url' => asset('images/teacher.png'),
                    'path' => 'images/teacher.png',
                    'provider' => 'static',
                ],
                'row_index' => 1,
                'column_index' => 2,
                'colspan' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($samples as $sample) {
            Speech::query()->updateOrCreate(
                [
                    'row_index' => $sample['row_index'],
                    'column_index' => $sample['column_index'],
                ],
                $sample
            );
        }
    }
}

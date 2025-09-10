<?php
$active = "aboutus";
include('db.php');
include("functions.php");
include("header.php");
?>

<head>
    <link rel="stylesheet" href="css/aboutus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<!-- About Section Begin -->
<div class="about-section">
    <div class="container">
        <?php
        // Language-based about section translations
        $about_texts = [
            'en' => [
                'story_title' => 'Our Story',
                'story_subtitle' => 'Learn more about Edutourism.lk and our passion for education and travel.',
                'heading' => 'Leading Edutourism in Sri Lanka',
                'paragraph1' => 'Edutourism.lk is a leading Sri Lankan company specializing in inbound and edutourism programs, seamlessly blending education, travel, and cultural immersion to create transformative learning experiences. Established with a vision to empower students, educators, and professionals through global exposure, we are committed to fostering cross-cultural understanding, academic excellence, and personal growth.',
                'paragraph2' => 'Drawing inspiration from initiatives like the Commonwealth Youth Network of Sri Lanka (CYN Sri Lanka), the company aligns its programs with Sustainable Development Goals (SDGs), particularly SDG 4 (Quality Education), SDG 17 (Partnerships for the Goals), and SDG 10 (Reduced Inequalities), to promote a more inclusive and connected world.',
                'paragraph3' => 'Our team consists of experienced educators, travel experts, and cultural ambassadors who are passionate about creating memorable educational experiences that transform lives and open new horizons.',
                'mission_title' => 'Our Mission',
                'mission_text' => 'To provide innovative edutourism programs that combine experiential learning with travel, empowering participants to develop critical skills, cultural competence, and a global perspective.',
                'vision_title' => 'Our Vision',
                'vision_text' => 'To be the premier edutourism provider in Sri Lanka, uniting learners worldwide through immersive educational travel experiences that inspire lifelong growth and positive change.'
            ],
            'si' => [
                'story_title' => 'අපගේ කතාව',
                'story_subtitle' => 'Edutourism.lk සහ අධ්‍යාපනය හා සංචාරය සඳහා ඇති අපගේ ආශාව ගැන තව දැනගන්න.',
                'heading' => 'ශ්‍රී ලංකාවේ අධ්‍යාපන සංචාරක ක්ෂේත්‍රය ප්‍රමුඛ කරගැනීම',
                'paragraph1' => 'Edutourism.lk යනු අධ්‍යාපනය, සංචාරය සහ සංස්කෘතික ඇසුර නිසි ලෙස සංයෝජනය කරමින් පරිවර්තනීය ඉගෙනුම් අත්දැකීම් නිර්මාණය කරන ආගමන සහ අධ්‍යාපන සංචාරක වැඩසටහන් විශේෂඥයකු වන ප්‍රමුඛ ශ්‍රී ලාංකික සමාගමකි. ගෝලීය නිරාවරණය හරහා සිසුන්, අධ්‍යාපනඥයින් සහ වෘත්තිකයන් සවිබල ගැන්වීමේ දැක්මකින් ස්ථාපිත කරන ලද, අපි සංස්කෘතික අවබෝධය, අධ්‍යාපනික විශිෂ්ටත්වය සහ පෞද්ගලික වර්ධනය ප්‍රවර්ධනය කිරීමට කැපවී සිටිමු.',
                'paragraph2' => 'ශ්‍රී ලංකා පොදුරාජ්‍ය මණ්ඩලීය තරුණ ජාලය (CYN ශ්‍රී ලංකා) වැනි මුලපිරීම්වලින් අනුප්‍රේරණය ලබන සමාගම, වඩාත් අන්තර්ගත සහ සම්බන්ධිත ලෝකයක් ප්‍රවර්ධනය කිරීම සඳහා, විශේෂයෙන් SDG 4 (ගුණාත්මක අධ්‍යාපනය), SDG 17 (ඉලක්ක සඳහා හවුල්කාරිත්වය) සහ SDG 10 (අඩු විෂමතා) වැනි තිරසාර සංවර්ධන ඉලක්ක (SDGs) සමඟ එහි වැඩසටහන් පෙළගස්වයි.',
                'paragraph3' => 'අපගේ කණ්ඩායම ජීවිත පරිවර්තනය කරන සහ නව ක්ෂිතිජයන් විවෘත කරන අමතක නොවන අධ්‍යාපනික අත්දැකීම් නිර්මාණය කිරීමට උනන්දුවක් දක්වන පළපුරුදු අධ්‍යාපනඥයින්, සංචාරක විශේෂඥයින් සහ සංස්කෘතික තානාපතිවරුන්ගෙන් සමන්විත වේ.',
                'mission_title' => 'අපගේ මෙහෙවර',
                'mission_text' => 'සහභාගිවන්නන්ට විවේචනාත්මක කුසලතා, සංස්කෘතික නිපුණත්වය සහ ගෝලීය දැක්මක් වර්ධනය කිරීම සඳහා සවිබල ගැන්වීම සඳහා අත්දැකීම් ඉගෙනීම සංචාරය සමඟ ඒකාබද්ධ කරන නවමු අධ්‍යාපන සංචාරක වැඩසටහන් සැපයීමට.',
                'vision_title' => 'අපගේ දැක්ම',
                'vision_text' => 'ශ්‍රී ලංකාවේ ප්‍රමුඛතම අධ්‍යාපන සංචාරක සැපයුම්කරු වීමට, ජීවිත කාලීන වර්ධනය සහ ධනාත්මක වෙනසකට උද්යෝගය ලබා දෙන අන්තර්ග්‍රාහී අධ්‍යාපනික සංචාරක අත්දැකීම් හරහා ලොව පුරා ඉගෙනුම් ලබන්නන් එක්සත් කිරීම.'
            ]
        ];
        ?>
        <div class="section-title">
            <h2><?php echo $about_texts[$lang]['story_title']; ?></h2>
            <p><?php echo $about_texts[$lang]['story_subtitle']; ?></p>
        </div>

        <div class="about-content">
            <div class="about-text">
                <h3><?php echo $about_texts[$lang]['heading']; ?></h3>
                <p><?php echo $about_texts[$lang]['paragraph1']; ?></p>
                <p><?php echo $about_texts[$lang]['paragraph2']; ?></p>
                <p><?php echo $about_texts[$lang]['paragraph3']; ?></p>
            </div>
            <div class="about-image">
                <img src="img/other/leading.png" alt="Edutourism Experience">
            </div>
        </div>

        <div class="mission-vision">
            <div class="mission-box">
                <i class="fas fa-bullseye"></i>
                <h3><?php echo $about_texts[$lang]['mission_title']; ?></h3>
                <p><?php echo $about_texts[$lang]['mission_text']; ?></p>
            </div>
            <div class="vision-box">
                <i class="fas fa-binoculars"></i>
                <h3><?php echo $about_texts[$lang]['vision_title']; ?></h3>
                <p><?php echo $about_texts[$lang]['vision_text']; ?></p>
            </div>
        </div>
    </div>
</div>
<!-- About Section End -->

<!-- Stats Section Begin -->
<div class="stats-section">
    <div class="container">
        <?php
        // Language-based stats section translations
        $stats_texts = [
            'en' => [
                'members' => 'Members',
                'universities' => 'Universities',
                'associations' => 'Associations',
                'programs' => 'Programs Completed'
            ],
            'si' => [
                'members' => 'සාමාජිකයින්',
                'universities' => 'විශ්වවිද්‍යාල',
                'associations' => 'සංගම්',
                'programs' => 'සම්පූර්ණ කළ වැඩසටහන්'
            ]
        ];
        ?>
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number" data-count="14000">10+</div>
                <div class="stat-text"><?php echo $stats_texts[$lang]['members']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-count="14000">10+</div>
                <div class="stat-text"><?php echo $stats_texts[$lang]['universities']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-count="14000">10+</div>
                <div class="stat-text"><?php echo $stats_texts[$lang]['associations']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-count="200">10+</div>
                <div class="stat-text"><?php echo $stats_texts[$lang]['programs']; ?></div>
            </div>
        </div>
    </div>
</div>
<!-- Stats Section End -->

<!-- Services Section Begin -->
<div class="services-section">
    <div class="container">
        <?php
        // Language-based services section translations
        $services_texts = [
            'en' => [
                'title' => 'Our Core Services',
                'subtitle' => 'We offer a wide range of educational travel experiences both in Sri Lanka and abroad',
                'service1_title' => 'Inbound Edutourism Programs',
                'service1_desc' => 'We welcome international students, educators, and groups to Sri Lanka, offering curated programs that showcase the country\'s rich cultural heritage, natural beauty, and educational opportunities.',
                'service1_feature1' => 'Cultural Immersion Experiences',
                'service1_feature2' => 'Academic Collaborations',
                'service1_feature3' => 'Community Engagement Projects',
                'service1_feature4' => 'Customized Educational Itineraries',
                'service2_title' => 'Outbound Edutourism Programs',
                'service2_desc' => 'We design global educational tours for Sri Lankan students, educators, and professionals, providing access to international academic institutions, industries, and cultural experiences.',
                'service2_feature1' => 'International Study Tours',
                'service2_feature2' => 'Industry Exposure Visits',
                'service2_feature3' => 'Cross-Cultural Exchange Programs',
                'service2_feature4' => 'Professional Development Workshops',
                'service3_title' => 'Sustainable Tourism Initiatives',
                'service3_desc' => 'We are dedicated to sustainable tourism practices, ensuring minimal environmental impact and maximum community benefit through our programs.',
                'service3_feature1' => 'Support for Local Economies',
                'service3_feature2' => 'Eco-Friendly Travel Options',
                'service3_feature3' => 'SDG-Aligned Programs',
                'service3_feature4' => 'Cultural Preservation Efforts'
            ],
            'si' => [
                'title' => 'අපගේ ප්‍රධාන සේවාවන්',
                'subtitle' => 'අපි ශ්‍රී ලංකාව තුළ සහ විදේශයන්හි අධ්‍යාපනික සංචාරක අත්දැකීම් පුළුල් පරාසයක් ලබා දෙමු',
                'service1_title' => 'ආගමන අධ්‍යාපන සංචාරක වැඩසටහන්',
                'service1_desc' => 'අපි ජාත්‍යන්තර සිසුන්, අධ්‍යාපනඥයින් සහ කණ්ඩායම් ශ්‍රී ලංකාවට පිළිගනිමු, රටේ පොහොසත් සංස්කෘතික උරුමය, ස්වාභාවික සෞන්දර්යය සහ අධ්‍යාපනික අවස්ථා පෙන්වන සැකසූ වැඩසටහන් ලබා දෙමු.',
                'service1_feature1' => 'සංස්කෘතික ආශ්‍රිත අත්දැකීම්',
                'service1_feature2' => 'අධ්‍යාපනික සහයෝගීතා',
                'service1_feature3' => 'ප්‍රජා සහභාගිත්ව ව්‍යාපෘති',
                'service1_feature4' => 'අභිරුචිකරණය කළ අධ්‍යාපනික ගමන් සැලසුම්',
                'service2_title' => 'නික්මයාම අධ්‍යාපන සංචාරක වැඩසටහන්',
                'service2_desc' => 'අපි ශ්‍රී ලාංකික සිසුන්, අධ්‍යාපනඥයින් සහ වෘත්තිකයින් සඳහා ගෝලීය අධ්‍යාපන සංචාර සැලසුම් කරමු, ජාත්‍යන්තර අධ්‍යාපන ආයතන, කර්මාන්ත සහ සංස්කෘතික අත්දැකීම් වෙත ප්‍රවේශය සපයමින්.',
                'service2_feature1' => 'ජාත්‍යන්තර අධ්‍යාපන සංචාර',
                'service2_feature2' => 'කර්මාන්ත නිරාවරණ චාරිකා',
                'service2_feature3' => 'සංස්කෘතික හුවමාරු වැඩසටහන්',
                'service2_feature4' => 'වෘත්තීය සංවර්ධන වැඩමුළු',
                'service3_title' => 'තිරසාර සංචාරක මුලපිරීම්',
                'service3_desc' => 'අපි තිරසාර සංචාරක භාවිතයන්ට කැපවී සිටිමු, අපගේ වැඩසටහන් හරහා අවම පාරිසරික බලපෑමක් සහ උපරිම ප්‍රජා ප්‍රතිලාභයක් සහතික කරමින්.',
                'service3_feature1' => 'දේශීය ආර්ථිකයන් සඳහා සහාය',
                'service3_feature2' => 'පරිසර හිතකාමී සංචාරක විකල්ප',
                'service3_feature3' => 'තිරසාර සංවර්ධන ඉලක්ක සමඟ පෙළගැස්මේ වැඩසටහන්',
                'service3_feature4' => 'සංස්කෘතික සංරක්ෂණ උත්සාහයන්'
            ]
        ];
        ?>
        <div class="section-title">
            <h2><?php echo $services_texts[$lang]['title']; ?></h2>
            <p><?php echo $services_texts[$lang]['subtitle']; ?></p>
        </div>

        <div class="services-container">
            <div class="service-box">
                <div class="service-icon">
                    <i class="fas fa-globe-asia"></i>
                </div>
                <h3><?php echo $services_texts[$lang]['service1_title']; ?></h3>
                <p><?php echo $services_texts[$lang]['service1_desc']; ?></p>
                <ul class="service-features">
                    <li><?php echo $services_texts[$lang]['service1_feature1']; ?></li>
                    <li><?php echo $services_texts[$lang]['service1_feature2']; ?></li>
                    <li><?php echo $services_texts[$lang]['service1_feature3']; ?></li>
                    <li><?php echo $services_texts[$lang]['service1_feature4']; ?></li>
                </ul>
            </div>

            <div class="service-box">
                <div class="service-icon">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <h3><?php echo $services_texts[$lang]['service2_title']; ?></h3>
                <p><?php echo $services_texts[$lang]['service2_desc']; ?></p>
                <ul class="service-features">
                    <li><?php echo $services_texts[$lang]['service2_feature1']; ?></li>
                    <li><?php echo $services_texts[$lang]['service2_feature2']; ?></li>
                    <li><?php echo $services_texts[$lang]['service2_feature3']; ?></li>
                    <li><?php echo $services_texts[$lang]['service2_feature4']; ?></li>
                </ul>
            </div>

            <div class="service-box">
                <div class="service-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3><?php echo $services_texts[$lang]['service3_title']; ?></h3>
                <p><?php echo $services_texts[$lang]['service3_desc']; ?></p>
                <ul class="service-features">
                    <li><?php echo $services_texts[$lang]['service3_feature1']; ?></li>
                    <li><?php echo $services_texts[$lang]['service3_feature2']; ?></li>
                    <li><?php echo $services_texts[$lang]['service3_feature3']; ?></li>
                    <li><?php echo $services_texts[$lang]['service3_feature4']; ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Services Section End -->

<!-- Updated Team Section with Real Information -->
<div class="edu-team-section">
    <div class="container">
        <?php
    $team_texts = [
            'en' => [
                'title' => 'Meet Our Team',
                'subtitle' => 'The passionate individuals behind Edutourism.lk',
                'member1_name' => 'Mr. Gayan Rajapaksha',
                'member1_position' => 'Co-Founder | CEO',
                'member1_credentials' => 'MBA Project Management - Cardiff Metropolitan University, UK, SCPC - Coach Transformation Academy(UAE-ICF Recognized), PQHRM(CIPMSL)',
                'member1_bio' => 'INTERNATIONAL CERTIFIED TRAINING CONSULTANT, LEARNING FACILITATOR, MASTER TRAINER, CERTIFIED COACH, NON-FORMAL EDUCATION SPECIALIST, INTERNATIONAL YOUTH ACTIVIST',
                'member2_name' => 'Mr. Goyum Prabath',
                'member2_position' => 'Co-Founder | COO',
                'member2_credentials' => 'MBA(UK), Dip. HRM(NIBM), Dip. Counselling(SLF), Dip. PR & Mass Com(UoK)',
                'member2_bio' => 'TREASURER - SRI LANKA PROFESSIONAL PSYCHOLOGICAL COUNSELORS ASSOCIATION, NATIONAL ORGANIZER - SRI LANKA PHYSICALLY CHALLENGED CRICKET ASSOCIATION, EXECUTIVE COMMITTEE MEMBER - SRI LANKA PRESS ASSOCIATION'
            ],
            'si' => [
                'title' => 'අපගේ කණ්ඩායම හමුවන්න',
                'subtitle' => 'Edutourism.lk පිටුපස සිටින උනන්දුවක් දක්වන පුද්ගලයින්',
                'member1_name' => 'ගයාන් රාජපක්ෂ මහතා',
                'member1_position' => 'සම-නිර්මාතෘ | සභාපති',
                'member1_credentials' => 'MBA ව්‍යාපෘති කළමනාකරණය - කාඩිෆ් මෙට්‍රොපොලිටන් විශ්වවිද්‍යාලය, එක්සත් රාජධානිය, SCPC - කෝච් ට්‍රාන්ස්ෆෝමේෂන් ඇකඩමි (UAE-ICF පිළිගත්), PQHRM(CIPMSL)',
                'member1_bio' => 'ජාත්‍යන්තර සහතික ලත් පුහුණු උපදේශක, ඉගෙනුම් පහසුකම්කරු, ප්‍රධාන පුහුණුකරු, සහතික ලත් පුහුණුකරු, විධිමත් නොවන අධ්‍යාපන විශේෂඥ, ජාත්‍යන්තර තරුණ ක්‍රියාකාරිකයා',
                'member2_name' => 'ගොයුම් ප්‍රභාත් මහතා',
                'member2_position' => 'සම-නිර්මාතෘ | ලේකම්',
                'member2_credentials' => 'MBA(UK), Dip. HRM(NIBM), Dip. උපදේශනය(SLF), Dip. PR & මහජන සන්නිවේදනය(UoK)',
                'member2_bio' => 'භාණ්ඩාගාරික - ශ්‍රී ලංකා වෘත්තීය මනෝවිද්‍යාත්මක උපදේශක සංගමය, ජාතික සංවිධායක - ශ්‍රී ලංකා ශාරීරික අභියෝගාත්මක ක්‍රිකට් සංගමය, විධායක කමිටු සාමාජික - ශ්‍රී ලංකා මාධ්‍ය සංගමය'
            ]
        ]; ?>
        <div class="section-title">
            <h2><?php echo $team_texts[$lang]['title']; ?></h2>
            <p><?php echo $team_texts[$lang]['subtitle']; ?></p>
        </div>

        <div class="edu-team-container">
            <!-- Team Member 1 - Gayan Rajapaksha -->
            <div class="edu-team-member">
                <div class="edu-team-card">
                    <div class="edu-team-front">
                        <div class="edu-team-image">
                            <img src="img/team/gayan.png" alt="<?php echo $team_texts[$lang]['member1_name']; ?>">
                        </div>
                        <div class="edu-team-info">
                            <h3><?php echo $team_texts[$lang]['member1_name']; ?></h3>
                            <p class="edu-team-position"><?php echo $team_texts[$lang]['member1_position']; ?></p>
                            <p class="edu-team-credentials"><em><?php echo $team_texts[$lang]['member1_credentials']; ?></em></p>
                            <div class="edu-team-social">
                                <a href="https://lk.linkedin.com/in/gayanraj"><i class="fab fa-linkedin"></i></a>
                                <a href="https://x.com/gayanraj"><i class="fab fa-twitter"></i></a>
                                <a href="https://www.facebook.com/gayanyouth"><i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="edu-team-back">
                        <h4><?php echo $team_texts[$lang]['member1_name']; ?></h4>
                        <p class="edu-team-position"><?php echo $team_texts[$lang]['member1_position']; ?></p>
                        <p class="edu-team-bio"><?php echo $team_texts[$lang]['member1_bio']; ?></p>
                        <div class="edu-team-social">
                            <!-- <a href="#"><i class="fab fa-linkedin"></i></a> -->
                            <a href="https://lk.linkedin.com/in/gayanraj"><i class="fab fa-linkedin"></i></a>
                            <a href="https://x.com/gayanraj"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.facebook.com/gayanyouth"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Team Member 2 - Goyum Prabath -->
            <div class="edu-team-member">
                <div class="edu-team-card">
                    <div class="edu-team-front">
                        <div class="edu-team-image">
                            <img src="img/team/goyum.png" alt="<?php echo $team_texts[$lang]['member2_name']; ?>">
                        </div>
                        <div class="edu-team-info">
                            <h3><?php echo $team_texts[$lang]['member2_name']; ?></h3>
                            <p class="edu-team-position"><?php echo $team_texts[$lang]['member2_position']; ?></p>
                            <p class="edu-team-credentials"><em><?php echo $team_texts[$lang]['member2_credentials']; ?></em></p>
                            <div class="edu-team-social">
                            <a href="https://www.instagram.com/goyumrupasena/"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.facebook.com/goyum.rupasena"><i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="edu-team-back">
                        <h4><?php echo $team_texts[$lang]['member2_name']; ?></h4>
                        <p class="edu-team-position"><?php echo $team_texts[$lang]['member2_position']; ?></p>
                        <p class="edu-team-bio"><?php echo $team_texts[$lang]['member2_bio']; ?></p>
                        <div class="edu-team-social">
                        <a href="https://www.instagram.com/goyumrupasena/"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/goyum.rupasena"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Commitment Section Begin -->
<div class="commitment-section">
    <div class="container">
        <?php
        // Language-based commitment section translations
        $commitment_texts = [
            'en' => [
                'title' => 'Our Commitments',
                'subtitle' => 'The values that guide our educational travel programs',
                'commitment1_title' => 'Educational Excellence',
                'commitment1_desc' => 'We prioritize meaningful learning outcomes and academic quality in all our programs, ensuring participants gain valuable knowledge and skills.',
                'commitment2_title' => 'Cultural Respect',
                'commitment2_desc' => 'We foster mutual respect and cross-cultural understanding, celebrating diversity and promoting authentic cultural exchanges.',
                'commitment3_title' => 'Environmental Responsibility',
                'commitment3_desc' => 'We are committed to sustainable tourism practices that minimize our environmental footprint and contribute to conservation efforts.',
                'commitment4_title' => 'Community Impact',
                'commitment4_desc' => 'We design programs that positively impact local communities, supporting local economies and contributing to social development.'
            ],
            'si' => [
                'title' => 'අපගේ කැපවීම්',
                'subtitle' => 'අපගේ අධ්‍යාපනික සංචාරක වැඩසටහන් මග පෙන්වන අගයන්',
                'commitment1_title' => 'අධ්‍යාපනික විශිෂ්ටත්වය',
                'commitment1_desc' => 'අපි අපගේ සියලුම වැඩසටහන්වල අර්ථවත් ඉගෙනුම් ප්‍රතිඵල සහ අධ්‍යාපනික ගුණාත්මකභාවය ප්‍රමුඛතා දෙන අතර, සහභාගිවන්නන් වටිනා දැනුම සහ කුසලතා ලබා ගන්නා බව සහතික කරමු.',
                'commitment2_title' => 'සංස්කෘතික ගෞරවය',
                'commitment2_desc' => 'අපි අන්‍යෝන්‍ය ගෞරවය සහ සංස්කෘතීන් අතර අවබෝධය වර්ධනය කරන අතර, විවිධත්වය සැමරීම සහ සැබෑ සංස්කෘතික හුවමාරුව ප්‍රවර්ධනය කරමු.',
                'commitment3_title' => 'පාරිසරික වගකීම',
                'commitment3_desc' => 'අපගේ පාරිසරික පදසටහන අවම කරන සහ සංරක්ෂණ ප්‍රයත්නයන්ට දායක වන තිරසාර සංචාරක භාවිතයන්ට අපි කැපවී සිටිමු.',
                'commitment4_title' => 'ප්‍රජා බලපෑම',
                'commitment4_desc' => 'අපි දේශීය ප්‍රජාවන්ට ධනාත්මකව බලපාන, දේශීය ආර්ථිකයන්ට සහාය වන සහ සමාජ සංවර්ධනයට දායක වන වැඩසටහන් නිර්මාණය කරමු.'
            ]
        ];
        ?>
        <div class="section-title">
            <h2><?php echo $commitment_texts[$lang]['title']; ?></h2>
            <p><?php echo $commitment_texts[$lang]['subtitle']; ?></p>
        </div>

        <div class="commitment-container">
            <div class="commitment-box">
                <div class="commitment-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3><?php echo $commitment_texts[$lang]['commitment1_title']; ?></h3>
                <p><?php echo $commitment_texts[$lang]['commitment1_desc']; ?></p>
            </div>

            <div class="commitment-box">
                <div class="commitment-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3><?php echo $commitment_texts[$lang]['commitment2_title']; ?></h3>
                <p><?php echo $commitment_texts[$lang]['commitment2_desc']; ?></p>
            </div>

            <div class="commitment-box">
                <div class="commitment-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3><?php echo $commitment_texts[$lang]['commitment3_title']; ?></h3>
                <p><?php echo $commitment_texts[$lang]['commitment3_desc']; ?></p>
            </div>

            <div class="commitment-box">
                <div class="commitment-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo $commitment_texts[$lang]['commitment4_title']; ?></h3>
                <p><?php echo $commitment_texts[$lang]['commitment4_desc']; ?></p>
            </div>
        </div>
    </div>
</div>
<!-- Commitment Section End -->

<!-- Add script to handle flip animations for team members -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize counter animation for stats
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const count = parseInt(stat.dataset.count);
            let currentCount = 0;
            const increment = Math.ceil(count / 50);
            const duration = 4000; // 2 seconds
            const interval = duration / (count / increment);
            
            const counter = setInterval(() => {
                currentCount += increment;
                if (currentCount >= count) {
                    currentCount = count;
                    clearInterval(counter);
                }
                
                if (count >= 1000) {
                    stat.textContent = (currentCount / 1000).toFixed(1) + 'k+';
                } else {
                    stat.textContent = currentCount + '+';
                }
            }, interval);
        });
    });
</script>

<?php
include("footer.php");
?>
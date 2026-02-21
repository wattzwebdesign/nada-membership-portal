<?php

namespace Database\Seeders;

use App\Models\GlossaryCategory;
use App\Models\GlossaryTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GlossarySeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->getData();

        foreach ($categories as $sortOrder => $categoryData) {
            $category = GlossaryCategory::updateOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'sort_order' => $sortOrder + 1,
                ]
            );

            foreach ($categoryData['terms'] as $termSortOrder => $termData) {
                GlossaryTerm::updateOrCreate(
                    ['slug' => Str::slug($termData['term'])],
                    [
                        'glossary_category_id' => $category->id,
                        'term' => $termData['term'],
                        'definition' => $termData['definition'],
                        'sort_order' => $termSortOrder,
                        'is_published' => true,
                    ]
                );
            }
        }
    }

    private function getData(): array
    {
        return [
            // 1. NADA Protocol & Auricular Acupuncture (22 terms)
            [
                'name' => 'NADA Protocol & Auricular Acupuncture',
                'terms' => [
                    ['term' => 'NADA Protocol', 'definition' => 'A standardized auricular acupuncture treatment involving the gentle placement of up to five small, sterilized, disposable needles into specific sites on each ear. Developed at Lincoln Hospital in the South Bronx, NY, it is a non-verbal approach to healing used as an adjunct therapy for behavioral health, addiction, trauma, and stress.'],
                    ['term' => 'Acudetox', 'definition' => 'A synonym for the NADA protocol. The term refers to both the integrated style of auricular acupuncture treatment and the specific insertion of the five standardized ear points. It was originally designed for acute heroin withdrawal and has since expanded to broader behavioral health applications.'],
                    ['term' => 'Shen Men (Spirit Gate)', 'definition' => 'One of the five NADA protocol ear acupuncture points. It regulates excitation and inhibition of the cerebral cortex, producing a calming and sedative effect on the mind and body. In acupuncture tradition, it is considered the "spirit gateway."'],
                    ['term' => 'Sympathetic Point', 'definition' => 'One of the five NADA protocol ear acupuncture points. It addresses disruption in both the sympathetic and parasympathetic nervous systems, producing a strong analgesic and relaxant effect on internal organs by dilating blood vessels. Sometimes referred to as the "Autonomic" point.'],
                    ['term' => 'Kidney Point (Auricular)', 'definition' => 'One of the five NADA protocol ear acupuncture points. A strengthening point that can relieve mental weariness, fatigue, and headaches. In TCM theory, the Kidney stores Essence (Jing) and governs reproduction, growth, and development.'],
                    ['term' => 'Liver Point (Auricular)', 'definition' => 'One of the five NADA protocol ear acupuncture points. It addresses symptoms associated with poor liver functioning and inflammation. In TCM, the Liver governs the smooth flow of Qi and regulates emotions.'],
                    ['term' => 'Lung Point (Auricular)', 'definition' => 'One of the five NADA protocol ear acupuncture points. It is associated with analgesia, sweating, and various respiratory conditions. In TCM, the Lung governs Qi and respiration and plays a role in grief processing.'],
                    ['term' => 'Auricular Acupuncture', 'definition' => 'A technique that involves the stimulation of specific points on the external ear (auricle) to diagnose and treat various conditions throughout the body. The ear is used as an adjunct therapy when body acupuncture is the main treatment focus.'],
                    ['term' => 'Auriculotherapy', 'definition' => 'A health care modality in which only the external surface of the ear (auricle) is stimulated to alleviate pathological conditions in other parts of the body. Unlike general ear acupuncture, auriculotherapy uses the ear as the sole treatment site, employing Nogier phases for both diagnosis and treatment.'],
                    ['term' => 'Auriculomedicine', 'definition' => 'An advanced form of auricular therapy in which only the ear is used for both diagnosis and treatment, employing sophisticated diagnostic techniques including the Vascular Autonomic Signal (VAS), color and biological filters, various hammer tools, electro-point finders, and the seven Nogier frequencies.'],
                    ['term' => 'Microsystem', 'definition' => 'A self-contained system within the body that reflects or represents the entire body in miniature. In auricular acupuncture, the external ear is understood as a microsystem, with specific ear points corresponding to organs, body regions, and physiological functions throughout the body.'],
                    ['term' => 'Somatotopic Map', 'definition' => 'The representation of the body mapped onto the external ear, first described by Dr. Paul Nogier in 1957. The ear\'s somatotopic correspondences show the body in the pattern of an inverted fetus, with the earlobe corresponding to the head and the upper ear corresponding to the feet.'],
                    ['term' => 'Paul Nogier', 'definition' => 'A French physician (1908-1996) widely considered the father of modern auriculotherapy. In 1957, he first presented his observations of somatotopic correspondences of the ear, developed the inverted fetus model, and made three key discoveries: ear somatotopy, the Vascular Autonomic Signal, and stimulation frequencies.'],
                    ['term' => 'Michael Smith, MD, DAc', 'definition' => 'The physician who led the development and refinement of the NADA protocol at Lincoln Hospital in the South Bronx during the 1970s. Under his leadership, the five-point ear combination was established as the standard treatment protocol. He founded NADA as an organization.'],
                    ['term' => 'Lincoln Hospital (Lincoln Detox)', 'definition' => 'The hospital in the South Bronx, New York, where the NADA protocol was developed in the mid-1970s. Community activists, including members of the Young Lords and Black Panthers, helped establish the Lincoln Detox program to address the heroin epidemic, eventually incorporating acupuncture as a primary treatment method.'],
                    ['term' => 'Ear Seeds', 'definition' => 'Tiny seeds (traditionally vaccaria seeds) or metal beads affixed to the surface of the ear with adhesive tape to stimulate specific auricular acupuncture points. They provide sustained, gentle acupressure and can be left in place for several days, allowing patients to self-stimulate the points between clinical visits.'],
                    ['term' => 'ASP Needles', 'definition' => 'Small, semi-permanent, dart-shaped intradermal needles (Aiguilles Semi-Permanentes) used in auricular acupuncture protocols, including Battlefield Acupuncture. They are inserted into auricular points and can remain in place for several days to provide ongoing stimulation.'],
                    ['term' => 'Nogier Phases', 'definition' => 'A diagnostic and treatment framework in auriculomedicine describing three phases of disease progression mapped onto the ear: Phase 1 (acute), Phase 2 (degenerative), and Phase 3 (chronic). Each phase corresponds to different regions and reflex patterns on the ear surface.'],
                    ['term' => 'Nogier Frequencies', 'definition' => 'Seven distinct electromagnetic frequencies identified by Paul Nogier, each believed to resonate with specific tissue types and physiological functions. They are used in auriculomedicine for targeted stimulation of auricular points.'],
                    ['term' => 'Vascular Autonomic Signal (VAS)', 'definition' => 'A pulse-based diagnostic technique used in auriculomedicine, discovered by Paul Nogier. It involves detecting subtle changes in the radial pulse in response to stimulation of auricular points, used to assess the body\'s bioenergetic field and identify active treatment points.'],
                    ['term' => 'Battlefield Acupuncture (BFA)', 'definition' => 'A specific auricular acupuncture protocol developed by Air Force physician Dr. Richard Niemtzow for rapid pain management in military settings. It uses five auricular points with ASP needles. Over 4,600 VA/DoD providers have been trained in the technique.'],
                    ['term' => 'Community Acupuncture', 'definition' => 'A model of acupuncture delivery in which treatment is provided in a group setting rather than in private individual sessions. The NADA protocol is typically delivered in this format, with participants sitting quietly together for 30 to 45 minutes, making treatment more accessible and affordable.'],
                ],
            ],

            // 2. Traditional Chinese Medicine (TCM) Fundamentals (22 terms)
            [
                'name' => 'Traditional Chinese Medicine (TCM) Fundamentals',
                'terms' => [
                    ['term' => 'Traditional Chinese Medicine (TCM)', 'definition' => 'A comprehensive system of medicine originating in China over 2,000 years ago. It encompasses acupuncture, herbal medicine, moxibustion, cupping, Tui Na massage, Qi Gong, Tai Chi, and dietary therapy. TCM is based on the concept that vital energy (Qi) flows through the body along pathways called meridians.'],
                    ['term' => 'Qi (Chi)', 'definition' => 'The fundamental life force or vital energy of the universe in Chinese philosophy and medicine. Qi permeates the whole body and is essential to all aspects of life. Health is understood as the smooth, balanced flow of Qi, while disease results from its disruption or deficiency.'],
                    ['term' => 'Yin and Yang', 'definition' => 'The two complementary, opposing forces that make up all aspects of life and Qi. Yin represents qualities such as coolness, rest, passivity, and interior, while Yang represents warmth, activity, brightness, and exterior. Health exists when Yin and Yang are in dynamic balance.'],
                    ['term' => 'Five Elements (Wu Xing)', 'definition' => 'A core TCM theory describing five phases of natural phenomena -- Wood, Fire, Earth, Metal, and Water -- and their interrelationships. Each element is associated with specific organs, emotions, seasons, colors, and flavors.'],
                    ['term' => 'Jing (Essence)', 'definition' => 'A fundamental substance in TCM stored primarily in the Kidneys. Jing governs growth, reproduction, development, and aging. It is considered a finite, inherited resource (Pre-Heaven Jing) that can be supplemented through nutrition and lifestyle (Post-Heaven Jing).'],
                    ['term' => 'Shen (Spirit/Mind)', 'definition' => 'In TCM, the spirit or consciousness that resides in the Heart. Shen encompasses mental activity, consciousness, memory, thinking, and sleep. Disturbance of the Shen can manifest as anxiety, insomnia, confusion, or emotional instability.'],
                    ['term' => 'Blood (Xue)', 'definition' => 'In TCM, Blood is a dense, material form of Qi that nourishes and moistens the body\'s tissues and organs. It is closely related to but distinct from the Western biomedical concept of blood. Blood is produced primarily by the Spleen and Stomach from food.'],
                    ['term' => 'Body Fluids (Jin Ye)', 'definition' => 'All normal liquids in the body other than Blood, including sweat, saliva, gastric juices, and joint fluid. Jin refers to thin, clear fluids that moisten the skin and muscles; Ye refers to thick, heavy fluids that lubricate the joints and nourish the brain.'],
                    ['term' => 'Zang-Fu Organs', 'definition' => 'The TCM organ system comprising five Yin (Zang) organs and six Yang (Fu) organs. Unlike Western anatomical organs, Zang-Fu represent functional systems of physiological relationships. The five Zang-Fu pairings are: Heart/Small Intestine, Spleen/Stomach, Lung/Large Intestine, Kidney/Bladder, and Liver/Gallbladder.'],
                    ['term' => 'Heart (Xin)', 'definition' => 'A Zang (Yin) organ in TCM that governs Blood circulation and houses the Shen (spirit/mind). It controls mental activities, emotions, and consciousness. The Heart is often called the "Emperor" of the body.'],
                    ['term' => 'Liver (Gan)', 'definition' => 'A Zang (Yin) organ in TCM that stores Blood and ensures the smooth flow of Qi throughout the body. It regulates emotions, supports decision-making, and governs the tendons. Liver Qi stagnation is one of the most common patterns of disharmony.'],
                    ['term' => 'Spleen (Pi)', 'definition' => 'A Zang (Yin) organ in TCM that governs the transformation and transportation of food and fluids into Qi and Blood. It is central to digestion and the production of energy. The Spleen also holds Blood in the vessels.'],
                    ['term' => 'Lung (Fei)', 'definition' => 'A Zang (Yin) organ in TCM that governs Qi and respiration. It controls the skin, body hair, and immune function (Wei Qi). The Lung is associated with grief and is the first organ affected by external pathogens.'],
                    ['term' => 'Kidney (Shen)', 'definition' => 'A Zang (Yin) organ in TCM that stores Essence (Jing) and governs reproduction, growth, aging, and bone development. The Kidney is the root of both Yin and Yang for the entire body and governs water metabolism.'],
                    ['term' => 'Pericardium (Xin Bao)', 'definition' => 'Known as the "Heart Protector" in TCM, the Pericardium acts as a protective shield for the Heart, absorbing emotional and pathogenic stress. Its meridian runs from the chest to the middle fingertip. It is paired with the San Jiao (Triple Burner).'],
                    ['term' => 'San Jiao (Triple Burner)', 'definition' => 'A unique Fu (Yang) organ concept in TCM that "has a name but no form." It represents a functional energy system coordinating the body\'s three main cavities: the Upper Burner (chest/respiration), Middle Burner (abdomen/digestion), and Lower Burner (pelvis/elimination).'],
                    ['term' => 'Wei Qi (Defensive Qi)', 'definition' => 'The protective energy in TCM that circulates on the surface of the body, guarding against external pathogens. Wei Qi warms the skin, regulates the opening and closing of pores, and is closely governed by the Lung.'],
                    ['term' => 'Yin Deficiency', 'definition' => 'A pattern of disharmony in TCM where the body\'s Yin (cooling, moistening, calming) substances are depleted. Symptoms include night sweats, dry mouth, heat sensations, restlessness, and insomnia. In the NADA protocol context, Yin deficiency with "empty fire" is a presumptive diagnosis underlying addiction.'],
                    ['term' => 'Empty Fire (Xu Re)', 'definition' => 'A condition in TCM where signs of Heat appear without true Yang excess, resulting from depleted Yin failing to anchor and cool the Yang. Symptoms include irritability, insomnia, night sweats, and a flushed face. This is a central concept in understanding the pathology of addiction.'],
                    ['term' => 'Qi Stagnation (Qi Zhi)', 'definition' => 'A disruption or slowing of the natural flow of Qi in the body. It can manifest physically as pain, distension, or stiffness, and emotionally as irritability, frustration, or depression. Liver Qi stagnation is the most common form.'],
                    ['term' => 'Blood Stagnation (Xue Yu)', 'definition' => 'A pattern in which Blood flow is impaired or obstructed, leading to fixed, stabbing pain, dark complexion, or masses. Blood stagnation is considered a more serious pathogenic factor than Qi stagnation and is associated with cardiovascular disease and chronic pain.'],
                    ['term' => 'Eight Principles (Ba Gang)', 'definition' => 'The fundamental diagnostic framework in TCM for classifying patterns of disharmony. The eight principles are organized in four pairs: Yin/Yang, Interior/Exterior, Cold/Heat, and Deficiency/Excess. They form the basis for all TCM pattern differentiation.'],
                ],
            ],

            // 3. Acupuncture Points & Meridians (16 terms)
            [
                'name' => 'Acupuncture Points & Meridians',
                'terms' => [
                    ['term' => 'Acupuncture Point (Xue Wei)', 'definition' => 'Specific locations on the body where Qi can be accessed and manipulated through needling, pressure, or other stimulation. There are over 360 classical acupuncture points located along the meridians, plus numerous extra points.'],
                    ['term' => 'Meridian (Jing Luo)', 'definition' => 'The pathways through which Qi, Blood, and body fluids flow throughout the body, connecting internal organs to the surface and to each other. The meridian system comprises 12 primary meridians, 8 extraordinary vessels, and numerous collateral channels.'],
                    ['term' => 'Twelve Primary Meridians', 'definition' => 'The twelve main channels of Qi flow in the body, each associated with a specific Zang-Fu organ. They are divided into six Yin meridians (Lung, Spleen, Heart, Kidney, Pericardium, Liver) and six Yang meridians (Large Intestine, Stomach, Small Intestine, Bladder, San Jiao, Gallbladder).'],
                    ['term' => 'Extraordinary Vessels', 'definition' => 'Eight special meridians that act as reservoirs for Qi and Blood, regulating the flow of the twelve primary meridians. The most important are the Ren Mai (Conception Vessel) and the Du Mai (Governing Vessel).'],
                    ['term' => 'Ren Mai (Conception Vessel)', 'definition' => 'An extraordinary vessel that runs along the midline of the front of the body from the perineum to the lower lip. It governs all the Yin meridians and is closely associated with reproduction and nourishing Yin energy.'],
                    ['term' => 'Du Mai (Governing Vessel)', 'definition' => 'An extraordinary vessel that runs along the midline of the back of the body from the coccyx up the spine and over the head to the upper lip. It governs all the Yang meridians and is associated with the brain, spinal cord, and Yang energy.'],
                    ['term' => 'Collateral Channels (Luo Mai)', 'definition' => 'Smaller branches of the meridian system that connect Yin-Yang paired meridians and distribute Qi and Blood to areas not directly covered by the primary meridians. They form a network extending the reach of the main channels.'],
                    ['term' => 'Ah Shi Points', 'definition' => 'Tender or painful spots on the body that are not fixed acupuncture points but are used as treatment points based on the patient\'s sensitivity. The name translates roughly to "Oh yes!" -- the exclamation a patient makes when a sore point is pressed.'],
                    ['term' => 'De Qi (Arrival of Qi)', 'definition' => 'The sensation experienced during acupuncture needling that indicates the needle has contacted the Qi. It typically feels like numbness, distension, heaviness, or an electrical tingling at the needling site. Achieving De Qi is traditionally considered necessary for effective treatment.'],
                    ['term' => 'Cun (Body Inch)', 'definition' => 'A proportional unit of measurement used in acupuncture to locate points on the body. One cun is typically defined as the width of the patient\'s thumb at the widest point, ensuring point location is proportional to each individual patient\'s anatomy.'],
                    ['term' => 'Pattern Differentiation (Bian Zheng)', 'definition' => 'The TCM diagnostic process of analyzing clusters of signs and symptoms to identify underlying patterns of disharmony in Qi, Blood, Yin, Yang, and body fluids. Rather than isolating a single disease, practitioners examine the whole picture to determine root cause.'],
                    ['term' => 'Dampness (Shi)', 'definition' => 'A pathological condition in TCM characterized by heaviness, sluggishness, swelling, and turbidity. Dampness obstructs the clear Yang Qi and is primarily related to dysfunction of the Spleen system.'],
                    ['term' => 'Phlegm (Tan)', 'definition' => 'A pathological product in TCM that can be "substantial" (visible mucus) or "insubstantial" (invisible phlegm manifesting as mental cloudiness, nodules, or dizziness). Phlegm is produced when body fluids are not properly transformed, often due to Spleen or Lung dysfunction.'],
                    ['term' => 'Wind (Feng)', 'definition' => 'A pathogenic factor in TCM characterized by sudden onset, rapid changes, and movement. External Wind enters the body through open pores and meridians, while Internal Wind arises from disturbance in Yang Qi. Symptoms include tremors, spasms, dizziness, and wandering pain.'],
                    ['term' => 'Cold (Han)', 'definition' => 'A Yin pathogenic factor in TCM characterized by contraction, slowing, and obstruction. Cold slows the movement of Qi and Blood, causing pain, stiffness, and pale complexion. It can be external or internal (from Yang deficiency).'],
                    ['term' => 'Heat (Re)', 'definition' => 'A Yang pathogenic factor in TCM characterized by inflammation, redness, thirst, rapid pulse, and agitation. Heat can be caused by external pathogens, emotional factors, or dietary excesses. Excess Heat involves true surplus of Yang, while Empty Heat results from Yin deficiency.'],
                ],
            ],

            // 4. Treatment Modalities (16 terms)
            [
                'name' => 'Treatment Modalities',
                'terms' => [
                    ['term' => 'Acupuncture', 'definition' => 'A TCM technique involving the insertion of thin, sterile, disposable stainless steel needles into specific points on the body to balance the flow of Qi, relieve pain, and treat various conditions. It is one of the seven pillars of Traditional Chinese Medicine and has been practiced for over 2,000 years.'],
                    ['term' => 'Moxibustion (Jiu)', 'definition' => 'A TCM treatment involving the burning of dried mugwort herb (Artemisia vulgaris), known as moxa, near or on the skin at acupuncture points. It stimulates circulation, counteracts cold and dampness, promotes the flow of Qi and Blood, and strengthens Yang.'],
                    ['term' => 'Cupping (Ba Guan)', 'definition' => 'A therapeutic technique using glass, ceramic, bamboo, or plastic cups applied to the skin with suction. Cupping draws superficial tissue into the cup, increases local blood circulation, relieves muscle tension, and promotes Qi flow.'],
                    ['term' => 'Gua Sha (Scraping)', 'definition' => 'A TCM technique in which a smooth-edged instrument is used to apply gentle, repeated scraping strokes to the skin. It releases muscle tension and constriction, promotes blood circulation, and moves stagnant Qi. Temporary redness or petechiae (sha) at the treatment site are normal.'],
                    ['term' => 'Electroacupuncture', 'definition' => 'A modern modification of traditional acupuncture in which a low-frequency electrical current is passed between pairs of inserted acupuncture needles. It enhances the stimulation of acupuncture points, increases blood flow, and reduces pain.'],
                    ['term' => 'Tui Na', 'definition' => 'A form of Chinese therapeutic bodywork (massage) that applies pressure to acupoints, meridians, and muscle groups to remove blockages and restore the free flow of Qi. Techniques include pressing, rolling, kneading, and stretching.'],
                    ['term' => 'Qi Gong (Chi Kung)', 'definition' => 'A mind-body practice combining gentle, intentional movements, coordinated breathing exercises, and meditation to cultivate and balance Qi. It is used both as a personal health practice and as a clinical modality.'],
                    ['term' => 'Tai Chi (Taijiquan)', 'definition' => 'A Chinese martial art and health practice using slow, controlled, flowing movements, focused breathing, and mental concentration to restore body balance, improve circulation, and cultivate Qi.'],
                    ['term' => 'Chinese Herbal Medicine', 'definition' => 'A major branch of TCM using plant, mineral, and animal substances prescribed in formulas tailored to a patient\'s specific pattern of disharmony. It follows the same diagnostic principles as acupuncture.'],
                    ['term' => 'Chinese Dietary Therapy (Shi Liao)', 'definition' => 'A branch of TCM that uses the therapeutic properties of foods to prevent and treat disease. Foods are classified by their temperature, flavor, and their effects on specific organs and meridians.'],
                    ['term' => 'Materia Medica (Ben Cao)', 'definition' => 'A comprehensive reference text cataloging medicinal substances used in TCM, including information on their properties, traditional uses, dosages, and contraindications. The most famous is the "Ben Cao Gang Mu" (Compendium of Materia Medica) by Li Shizhen.'],
                    ['term' => 'Herbal Formula (Fang Ji)', 'definition' => 'A combination of medicinal substances prescribed together in TCM. Formulas typically contain 9 to 18 substances organized by role: chief (Jun), deputy (Chen), assistant (Zuo), and envoy (Shi).'],
                    ['term' => 'Decoction (Tang)', 'definition' => 'The most common method of preparing Chinese herbal medicine. Dried herbs are boiled in water for an extended period, strained, and the resulting liquid is consumed. Decoctions offer rapid absorption, quick onset of effect, and flexible dosing.'],
                    ['term' => 'Patent Medicine (Zhong Cheng Yao)', 'definition' => 'Pre-manufactured Chinese herbal medicines available in ready-to-use forms such as tablets, pills, capsules, oral solutions, or granules. The term "patent" refers to standardized, commercially produced formulations.'],
                    ['term' => 'Acupressure', 'definition' => 'A technique that applies physical pressure to the same acupuncture points used in needling, using fingers, thumbs, palms, or devices instead of needles. It is used to stimulate Qi flow and relieve pain without penetrating the skin.'],
                    ['term' => 'Tonification', 'definition' => 'In the context of the NADA protocol, the long-term, preventative strengthening effect achieved through repeated auricular acupuncture treatments. The five-point ear combination was designed to offer both relief from acute withdrawal symptoms and an ongoing tonifying effect that builds resilience.'],
                ],
            ],

            // 5. Auricular Anatomy (10 terms)
            [
                'name' => 'Auricular Anatomy',
                'terms' => [
                    ['term' => 'Auricle', 'definition' => 'The external, visible portion of the ear. Its anatomical structures include the helix, antihelix, tragus, antitragus, scaphoid fossa, triangular fossa, superior and inferior concha, and the lobe. Each region contains specific acupuncture points used in auricular therapy.'],
                    ['term' => 'Helix', 'definition' => 'The curved, outer rim of the auricle. In auricular acupuncture, points along the helix are used for treating allergies, inflammation, and certain pain conditions.'],
                    ['term' => 'Antihelix', 'definition' => 'The Y-shaped curved ridge on the auricle lying between the helix and the concha. It corresponds to the spine and trunk in the somatotopic map of the ear. Its two branches (superior and inferior crus) correspond to the lower and upper extremities.'],
                    ['term' => 'Tragus', 'definition' => 'The small, pointed projection of cartilage that partially covers the opening of the ear canal. In auricular acupuncture, points on the tragus correspond to the adrenal glands, nose, and pharynx, and are used for anti-inflammatory and endocrine effects.'],
                    ['term' => 'Antitragus', 'definition' => 'The small raised area of cartilage opposite the tragus, separated from it by the intertragic notch. In the somatotopic map, it corresponds to the head and brain. Points here are used for treating headaches, dizziness, and neurological conditions.'],
                    ['term' => 'Concha', 'definition' => 'The bowl-shaped depression of the auricle surrounding the ear canal opening, divided into the superior concha (cymba conchae) and inferior concha (cavum conchae). The concha corresponds to the internal organs in auricular mapping and contains many key treatment points.'],
                    ['term' => 'Scaphoid Fossa', 'definition' => 'The narrow, curved depression between the helix and the antihelix of the ear. In auricular acupuncture, this area corresponds to the upper extremities including the shoulder, elbow, wrist, and fingers.'],
                    ['term' => 'Triangular Fossa', 'definition' => 'The triangular depression between the two branches (superior and inferior crus) of the antihelix. In auricular mapping, it corresponds to the pelvic organs and is used for treating conditions of the reproductive system and lower abdomen.'],
                    ['term' => 'Earlobe', 'definition' => 'The soft, fleshy lower part of the auricle without cartilage. In the inverted fetus somatotopic map, the earlobe corresponds to the head and face, including the eyes, jaw, and teeth.'],
                    ['term' => 'Intertragic Notch', 'definition' => 'The U-shaped depression between the tragus and antitragus of the ear. In auricular acupuncture, this area contains important endocrine points and corresponds to the pituitary gland and hormonal regulation.'],
                ],
            ],

            // 6. TCM Diagnostic Methods (8 terms)
            [
                'name' => 'TCM Diagnostic Methods',
                'terms' => [
                    ['term' => 'Tongue Diagnosis (She Zhen)', 'definition' => 'A primary TCM diagnostic method in which the practitioner examines the tongue\'s color, shape, coating, moisture, and texture to assess the state of the internal organs and overall health. For example, a pale tongue may suggest Qi or Blood deficiency, a red tongue suggests Heat.'],
                    ['term' => 'Pulse Diagnosis (Mai Zhen)', 'definition' => 'A TCM diagnostic technique in which the practitioner palpates the radial artery at the wrist at three positions on each wrist to assess the quality, rhythm, and strength of the pulse. There are traditionally 28 recognized pulse qualities.'],
                    ['term' => 'Four Examinations (Si Zhen)', 'definition' => 'The four primary diagnostic methods used in TCM: inspection (Wang), auscultation and olfaction (Wen), inquiry (Wen), and palpation (Qie). Together they provide a comprehensive picture of the patient\'s condition.'],
                    ['term' => 'Asking Diagnosis (Wen Zhen)', 'definition' => 'The component of the Four Examinations in which the practitioner systematically inquires about the patient\'s chief complaint, symptoms, medical history, lifestyle, emotional state, sleep, appetite, and other relevant factors.'],
                    ['term' => 'Inspection (Wang Zhen)', 'definition' => 'The diagnostic method of observing the patient\'s overall appearance, complexion, demeanor, body build, and the condition of specific areas such as the tongue, eyes, and skin. It includes assessment of the patient\'s spirit (Shen) and vitality.'],
                    ['term' => 'Auscultation and Olfaction (Wen Zhen)', 'definition' => 'The diagnostic method involving listening to the patient\'s voice, breathing, cough, and other sounds, as well as noting any body odors. Changes in sound quality and smell can indicate specific patterns of disharmony.'],
                    ['term' => 'Palpation (Qie Zhen)', 'definition' => 'The diagnostic method of physically examining the patient through touch, including pulse diagnosis at the wrist, palpation of acupuncture points and meridians, and assessment of body temperature, moisture, and tenderness.'],
                    ['term' => 'Facial Diagnosis (Mian Zhen)', 'definition' => 'A TCM diagnostic technique that maps specific facial regions to internal organs. Changes in color, texture, or markings in different facial zones can indicate the condition of corresponding organs. For example, the area between the eyebrows relates to the Liver.'],
                ],
            ],

            // 7. Behavioral Health & Addiction / Detoxification (21 terms)
            [
                'name' => 'Behavioral Health & Addiction / Detoxification',
                'terms' => [
                    ['term' => 'Addiction', 'definition' => 'A primary, chronic, neurobiological disease with genetic, psychosocial, and environmental factors influencing its development. It is characterized by impaired control over substance use, compulsive use, continued use despite harm, and craving.'],
                    ['term' => 'Substance Use Disorder (SUD)', 'definition' => 'A clinical diagnosis defined by a problematic pattern of substance use involving compulsive use, impaired control, and negative emotional states. Severity is classified as mild, moderate, or severe based on the number of diagnostic criteria met.'],
                    ['term' => 'Opioid Use Disorder (OUD)', 'definition' => 'A specific form of substance use disorder involving problematic use of opioids (prescription painkillers, heroin, fentanyl) that leads to clinically significant impairment or distress. It is characterized by tolerance, withdrawal, craving, and inability to control use.'],
                    ['term' => 'Detoxification (Detox)', 'definition' => 'A medically supervised process of eliminating intoxicating or addictive substances from the body while managing withdrawal symptoms and providing metabolic stabilization. It is typically the first phase of addiction treatment.'],
                    ['term' => 'Withdrawal', 'definition' => 'The physical and mental symptoms that occur when a person reduces or stops using a substance they have become dependent on. Symptoms vary by substance and can include tremors, sweating, nausea, anxiety, insomnia, hallucinations, and seizures.'],
                    ['term' => 'Tolerance', 'definition' => 'A physiological state in which the body requires increasing amounts of a substance to achieve the desired effect, or in which there is a markedly diminished response to the same amount of the substance over time.'],
                    ['term' => 'Physical Dependence', 'definition' => 'A physiological state in which the body has adapted to the sustained presence of a substance and requires it for normal metabolic functioning. Abrupt cessation produces withdrawal symptoms. Physical dependence is distinct from addiction.'],
                    ['term' => 'Craving', 'definition' => 'A powerful psychological and neurological desire to consume a substance. In addiction medicine, craving is recognized as a symptom of neuroadaptation and is a diagnostic criterion for substance use disorders.'],
                    ['term' => 'Relapse', 'definition' => 'A return to substance use after a period of abstinence or remission. It is understood in modern addiction medicine as a common part of the chronic disease process, not a moral failing, and typically signals the need for treatment adjustment.'],
                    ['term' => 'Recovery', 'definition' => 'A process of change through which individuals improve their health and wellness, live self-directed lives, and strive to reach their full potential. SAMHSA emphasizes that recovery is an ongoing process, not a single event.'],
                    ['term' => 'Harm Reduction', 'definition' => 'A set of practical policies, programs, and practices that aim to reduce the negative health, social, and economic consequences associated with substance use without necessarily requiring abstinence. Examples include needle exchange programs and naloxone distribution.'],
                    ['term' => 'Medication-Assisted Treatment (MAT)', 'definition' => 'An evidence-based approach that combines FDA-approved medications (such as buprenorphine, methadone, or naltrexone) with counseling and behavioral therapies to treat substance use disorders.'],
                    ['term' => 'Naloxone (Narcan)', 'definition' => 'An opioid antagonist medication that can rapidly reverse an opioid overdose by blocking opioid receptors. It can restore normal breathing within two to three minutes. Available as a nasal spray or injection, it is a critical harm reduction tool.'],
                    ['term' => 'Co-Occurring Disorders (Dual Diagnosis)', 'definition' => 'The simultaneous presence of two or more disorders in the same individual, most commonly a mental health disorder alongside a substance use disorder. Integrated treatment addressing both conditions together is considered the standard of care.'],
                    ['term' => 'Trauma-Informed Care (TIC)', 'definition' => 'A framework for health care delivery that recognizes and responds to the signs, symptoms, and risks of trauma. It emphasizes physical, psychological, and emotional safety for both providers and patients, and seeks to prevent re-traumatization.'],
                    ['term' => 'Adverse Childhood Experiences (ACEs)', 'definition' => 'Traumatic events occurring before age 18, including abuse, neglect, and household dysfunction. A higher ACE score is associated with increased risk of chronic disease, mental health conditions, substance use disorders, and reduced life expectancy.'],
                    ['term' => 'Stages of Change', 'definition' => 'A model describing the readiness progression for behavioral change: pre-contemplation (unaware), contemplation (considering change), preparation (planning), action (actively modifying behavior), and maintenance (sustaining change). Widely used in addiction treatment.'],
                    ['term' => 'Motivational Interviewing (MI)', 'definition' => 'A collaborative, person-centered counseling approach designed to strengthen an individual\'s own motivation and commitment to change. It addresses ambivalence about behavior change through empathy, reflective listening, and eliciting the person\'s own reasons for change.'],
                    ['term' => 'Cognitive Behavioral Therapy (CBT)', 'definition' => 'A structured, evidence-based psychotherapy that helps individuals identify and change negative thought patterns and behaviors. In addiction treatment, CBT teaches coping strategies, relapse prevention skills, and helps patients recognize triggers.'],
                    ['term' => 'Contingency Management', 'definition' => 'A behavioral intervention that provides or withholds tangible rewards (vouchers, prizes, privileges) based on measurable behaviors such as negative drug tests or treatment attendance. It is an evidence-based approach for reinforcing abstinence.'],
                    ['term' => 'Peer Support / Recovery Coach', 'definition' => 'A person with lived experience of recovery from a substance use disorder or mental health condition who provides support, mentorship, and resource navigation to others in or seeking recovery.'],
                ],
            ],

            // 8. Licensing, Credentialing & Professional Organizations (16 terms)
            [
                'name' => 'Licensing, Credentialing & Professional Organizations',
                'terms' => [
                    ['term' => 'Acupuncture Detoxification Specialist (ADS)', 'definition' => 'A practitioner who has completed NADA-approved training in the five-point ear acupuncture protocol. ADS practitioners include counselors, social workers, nurses, psychologists, case managers, physicians, and licensed acupuncturists working within their scope of practice.'],
                    ['term' => 'NADA Registered Trainer (RT)', 'definition' => 'An experienced NADA-trained practitioner authorized by NADA to conduct ADS training workshops. Only trainers listed on the official NADA website are authorized to provide certified training and submit trainee documentation for NADA approval.'],
                    ['term' => 'NADA Certificate of Training Completion', 'definition' => 'The credential issued by NADA to individuals who successfully complete ADS training under a NADA Registered Trainer, including both classroom instruction and a hands-on clinical practicum. This is not a "certification" in the formal credentialing sense.'],
                    ['term' => 'Licensed Acupuncturist (L.Ac.)', 'definition' => 'A healthcare professional whose primary training is in acupuncture and Oriental medicine, who has completed a master\'s-level degree from an ACAOM-accredited school and met state licensing requirements. Licensing criteria vary by state.'],
                    ['term' => 'Diplomate (Dipl. Ac., Dipl. OM)', 'definition' => 'A professional designation awarded by the NCCAOM upon successful completion of board examinations and other requirements. Diplomate in Acupuncture (Dipl. Ac.) requires a master\'s degree and passing board exams in Foundations, Biomedicine, and Acupuncture.'],
                    ['term' => 'NCCAOM', 'definition' => 'The National Certification Commission for Acupuncture and Oriental Medicine. The national organization that validates entry-level competency through standardized examinations and credential maintenance. NCCAOM certification is required for licensure in 46 states plus DC.'],
                    ['term' => 'ACAOM / ACAHM', 'definition' => 'The Accreditation Commission for Acupuncture and Oriental Medicine (now Acupuncture and Herbal Medicine). The recognized accrediting body for acupuncture and Oriental medicine programs in the United States. Graduation from an accredited school is required for licensure in most states.'],
                    ['term' => 'AAAOM', 'definition' => 'The American Association of Acupuncture and Oriental Medicine. A national professional membership association for acupuncture and Oriental medicine practitioners in the United States.'],
                    ['term' => 'PDA (Professional Development Activity)', 'definition' => 'The continuing education program through which Diplomates earn continuing education credits for participating in approved professional development programs. PDA points are required for recertification.'],
                    ['term' => 'CEU (Continuing Education Unit)', 'definition' => 'A standard unit of measurement for continuing professional education in healthcare. Acupuncturists and other licensed practitioners are typically required to complete a specified number of CEUs per renewal cycle to maintain licensure.'],
                    ['term' => 'Clean Needle Technique (CNT)', 'definition' => 'The standard safety protocol that acupuncturists follow to reduce the risk of adverse events, including exposure to bloodborne pathogens. Key practices include using sterile disposable needles, maintaining clean skin at insertion sites, and using a new needle for each insertion.'],
                    ['term' => 'NADA Ethics Pledge', 'definition' => 'A signed commitment required of all NADA ADS trainees, pledging to uphold confidentiality, respectful client interaction, adherence to a limited scope of practice, transparency in practice, and sharing of experiences within the NADA community.'],
                    ['term' => 'Scope of Practice', 'definition' => 'The range of procedures, activities, and processes that a licensed or credentialed healthcare provider is legally authorized to perform. For ADS practitioners, the scope is specifically limited to the NADA five-point ear acupuncture protocol and varies by jurisdiction.'],
                    ['term' => 'SAMHSA', 'definition' => 'The Substance Abuse and Mental Health Services Administration, a division of the U.S. Department of Health and Human Services. SAMHSA leads public health efforts to advance behavioral health and provides guidance, funding, data, and resources for substance use disorder and mental health treatment.'],
                    ['term' => 'POCA', 'definition' => 'People\'s Organization of Community Acupuncture. A cooperative organization that promotes the community acupuncture model of delivering affordable group-setting acupuncture treatments. While distinct from NADA, it shares the philosophy of accessible acupuncture.'],
                    ['term' => 'Behavioral Health', 'definition' => 'The broad field of health care concerned with the prevention, diagnosis, and treatment of substance use disorders, mental health conditions, and their related behavioral patterns. It encompasses both clinical treatment and community-based support services.'],
                ],
            ],

            // 9. Clinical Practice (15 terms)
            [
                'name' => 'Clinical Practice',
                'terms' => [
                    ['term' => 'Informed Consent', 'definition' => 'The process by which a patient receives full information about the recommended treatment, including its benefits, risks, alternatives, and potential effects of declining treatment, and provides voluntary agreement to proceed.'],
                    ['term' => 'Adverse Event', 'definition' => 'An unintended, undesirable outcome associated with acupuncture treatment. Common minor adverse events include bruising, numbness, tingling, or dizziness. Rare serious adverse events include nerve damage, organ puncture, or infection.'],
                    ['term' => 'Standard Precautions', 'definition' => 'A set of infection-control practices used in healthcare to prevent transmission of diseases through blood and body fluids. In acupuncture practice, this includes hand hygiene, personal protective equipment, safe needle handling and disposal, and surface disinfection.'],
                    ['term' => 'Needle Retention', 'definition' => 'The period during which acupuncture needles remain inserted in the body during treatment, typically 10 to 30 minutes. During needle retention, the patient rests while the needles provide ongoing stimulation.'],
                    ['term' => 'Needle Gauge and Length', 'definition' => 'Acupuncture needles are classified by their diameter (gauge) and length, selected based on the treatment location, depth needed, and patient characteristics. Common gauges range from 30 to 40, and lengths from 13mm to 75mm.'],
                    ['term' => 'Group Treatment Setting', 'definition' => 'The typical delivery format for the NADA protocol, in which multiple patients receive ear acupuncture simultaneously in a shared, calm environment. Participants sit in chairs for 30 to 45 minutes. This model increases accessibility and affordability.'],
                    ['term' => 'Contraindication', 'definition' => 'A condition or factor that makes a particular treatment inadvisable. In acupuncture, relative contraindications may include certain skin conditions at the needle site, hemophilia, or specific needle-sensitive areas.'],
                    ['term' => 'Adjunct Therapy', 'definition' => 'A treatment used alongside a primary treatment to enhance its effectiveness. The NADA protocol is explicitly described as an adjunct therapy, designed to be integrated into a comprehensive behavioral health treatment program rather than used standalone.'],
                    ['term' => 'Integrative Medicine', 'definition' => 'An approach to healthcare that combines conventional Western medicine with evidence-based complementary therapies, including acupuncture, to address the full range of physical, emotional, mental, social, and spiritual factors that affect health.'],
                    ['term' => 'Complementary and Alternative Medicine (CAM)', 'definition' => 'Health care approaches that are not considered part of conventional Western medicine. "Complementary" refers to practices used alongside conventional treatment; "alternative" refers to practices used in place of conventional treatment.'],
                    ['term' => 'Treatment Plan', 'definition' => 'An individualized plan developed by a healthcare provider that outlines the patient\'s identified needs, treatment goals, specific interventions, frequency and duration of treatment, and criteria for measuring progress.'],
                    ['term' => 'Practicum', 'definition' => 'The hands-on clinical training component required for NADA ADS certification. Trainees practice the NADA protocol under supervision in a clinical setting after completing classroom instruction. Documentation is submitted to NADA for final approval.'],
                    ['term' => 'Qi Sensation', 'definition' => 'The subjective experience reported by patients during acupuncture treatment, which may include warmth, tingling, heaviness, distension, or a dull ache at or around the needle site. It is closely related to De Qi and is generally considered a positive treatment indicator.'],
                    ['term' => 'Polyvagal Theory', 'definition' => 'A neurobiological theory developed by Dr. Stephen Porges explaining how the vagus nerve and autonomic nervous system regulate social engagement, emotional responses, and threat perception. It helps explain why trauma survivors may feel stuck in fight, flight, or freeze states.'],
                    ['term' => 'Dialectical Behavior Therapy (DBT)', 'definition' => 'A skills-based psychotherapy that teaches mindfulness, emotion regulation, distress tolerance, and interpersonal effectiveness. Originally developed for borderline personality disorder, it is widely used in behavioral health settings for co-occurring disorders.'],
                ],
            ],

            // 10. Disaster Relief & Special Applications (5 terms)
            [
                'name' => 'Disaster Relief & Special Applications',
                'terms' => [
                    ['term' => 'Disaster Relief Acupuncture', 'definition' => 'The application of the NADA protocol in disaster and humanitarian aid settings to treat populations affected by natural disasters, violence, and mass trauma. The protocol\'s non-verbal, group-based, minimal-resource format makes it particularly suited to crisis environments.'],
                    ['term' => 'NADA (National Acupuncture Detoxification Association)', 'definition' => 'A not-for-profit training and advocacy organization founded to promote and standardize the use of auricular acupuncture for behavioral health. NADA provides training, advocacy, and education to support the integration of the NADA protocol into addiction treatment, mental health services, and disaster relief.'],
                    ['term' => 'Lincoln Recovery Center', 'definition' => 'The clinical program at Lincoln Hospital in the South Bronx that continued the legacy of the Lincoln Detox program and became the primary site for the ongoing development and delivery of the NADA protocol under the leadership of Dr. Michael Smith.'],
                    ['term' => 'Young Lords / Black Panthers', 'definition' => 'Radical activist organizations whose members, in the early 1970s, occupied the sixth floor of Lincoln Hospital in the South Bronx to establish a drug treatment program (Lincoln Detox) in response to the heroin epidemic. Their activism laid the groundwork for the NADA protocol.'],
                    ['term' => 'Delirium Tremens (DTs)', 'definition' => 'A severe and potentially life-threatening form of alcohol withdrawal involving sudden changes to the nervous system, confusion, rapid heartbeat, fever, and hallucinations. It typically occurs 48 to 72 hours after the last drink in individuals with heavy, prolonged alcohol use.'],
                ],
            ],
        ];
    }
}

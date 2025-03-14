<?php /* Template Name: No Style Page */ ?>

<!doctype html>
<html class="h-100" lang="en">
    <head>
        <title>Mon site | Akobo</title>
        <link rel="icon" type="image/png" href="https://akoboblog.wpcomstaging.com/wp-content/uploads/2025/02/favicon.png">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .carousel-inner > .carousel-item > img {
                width: 640px;
                height: 360px;
                object-fit: contain;
            }

            .carousel-inner {
                background-color: black;
            }

            .modal-footer {
                display: flex;
                justify-content: center;
            }

            /* The slider itself */
            .slider {
                -webkit-appearance: none;  /* Override default CSS styles */
                appearance: none;
                width: 90%;
                position: relative;
            }
            
            /* The slider handle (use -webkit- (Chrome, Opera, Safari, Edge) and -moz- (Firefox) to override default look) */
            .slider::-webkit-slider-thumb {
                -webkit-appearance: none; /* Override default look */
                appearance: none;
                transform: scale(1.75);
                cursor: pointer;
            }
            
            .slider::-moz-range-thumb {
                box-sizing: border-box;
                transform: scale(1.75);
                cursor: pointer;
            }

            .delete_tooltip {
                position: absolute;
                left: 0;
                right: 0;
                top: 15px;
                margin-inline: auto;
                width: fit-content;
            }

            .btn-file {
                position: relative;
                overflow: hidden;
            }

            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;   
                cursor: inherit;
                display: block;
            }

            #loading_image_icon {
                width: 40px;
                height: 40px;
                position: absolute;
                left: 0;
                right: 0;
                top: 160px;
                margin-inline: auto;
                visibility: hidden;
            }

            .alert-success {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                margin-inline: auto;
                width: fit-content;
                -webkit-user-select: none; /* Safari */
                -ms-user-select: none; /* IE 10 and IE 11 */
                user-select: none; /* Standard syntax */
            }

            .card-header {
                background-color: #0d6efd;
            }

            .nav-link {
                color: #fff;
            }

            .nav-link:hover {
                color: #d3d4d5;
            }

            .nav-link.active {
                background-color: #f8f9fa !important;
                color: #000 !important;
            }

            .logo {
                background: url("logo.png") 50% 50%;
                background-size: contain;
                background-repeat: no-repeat;
            }

            #any_questions {
                color: #0d6efd;
                cursor: pointer;
            }

            #any_questions:hover {
                color: #0a58ca;
            }

            .picture_card:hover img {
                -webkit-filter: brightness(80%);
                filter: brightness(80%);
            }

            .picture_card:hover .card-img-overlay h5 {
                visibility: visible;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <?php the_content(); ?>
        <script>
            let current_img_field_id = 0; //each image field of the form has a unique id used as a selector for the array "selected_images"
            let selected_images = [
                params.image_accueil,
                params.esprit[0].Img,
                params.esprit[1].Img,
                params.galerie_images[0],
                params.galerie_images[1],
                params.galerie_images[2],
                params.galerie_images[3],
                params.galerie_images[4],
                params.galerie_images[5],
                params.contact_image
            ].map((x) => params.bibliotheque.findIndex((elt) => elt == x)); //array of images ids that are currently selected for each image field
            const max_images = 20;
            let images_list = params.bibliotheque;
            let images_list_up_to_date_in_db = true;
            let carousel;
            let nb_slides_spans;
            let delete_buttons;
            let select_buttons;
            let upload_span;
            let tooltipTriggerList;
            let tooltipList;
            let upload_tooltip;
            let range_elt;
            let current_section = 1;
            const max_fields = 12;
            let nb_fields_value = 0;
            let nb_fields_span;
            let copy_link_btn;
            let save_btn;
            let published_modal;
            const img_field_id_to_html_id = [ //array that associates image field id to its HTML element id
                "welcomePictureSection1",
                "picture1Section2",
                "picture2Section2",
                "picture1Section4",
                "picture2Section4",
                "picture3Section4",
                "picture4Section4",
                "picture5Section4",
                "picture6Section4",
                "contactPictureSection5"
            ];
            let current_theme = params.akobo_template;
            let previewed_theme;
            const themes_names_and_images = [
                ["Fluffy", "https://akobo.fr/wp-content/uploads/2025/03/template1.png"],
                ["Baron", "https://akobo.fr/wp-content/uploads/2025/03/template2.png"],
                ["Personnalisé", "https://akobo.fr/wp-content/uploads/2025/03/custom.png"]
            ];
            let theme_modal;

            document.addEventListener("DOMContentLoaded", (event) => {
                nb_slides_spans = document.getElementsByClassName("nb_slides");
                delete_buttons = document.getElementsByClassName("delete_btn");
                select_buttons = document.getElementsByClassName("select_btn");
                upload_span = document.getElementById("upload_span");
                nb_fields_span = document.getElementById("nb_fields");
                copy_link_btn = document.getElementById("copy_link_btn");
                save_btn = document.getElementById("save_btn");
                published_modal = new bootstrap.Modal(document.getElementById('published_modal'));
                theme_modal = new bootstrap.Modal(document.getElementById('themeModal'));

                //initialize delete tooltips
                tooltipTriggerList = document.getElementsByClassName("delete_tooltip");
                tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

                //initialize upload tooltip and prevent upload if max images limit reached
                document.getElementById("upload_tooltip").setAttribute("data-bs-title", max_images.toString()+" images max. Supprimez-en pour en recharger.");
                upload_tooltip = new bootstrap.Tooltip(document.getElementById("upload_tooltip"));
                upload_tooltip.disable();
                if (images_list.length == max_images) {
                    upload_tooltip.enable();
                    upload_span.classList.add("disabled");
                }
                
                update_nb_slides_spans();
                update_img_on_slides();
                update_selected_images(max_images);

                //hide unused slides
                for (let i = images_list.length; i < delete_buttons.length; i++) {
                    document.getElementById("item_"+i.toString()).classList.remove("carousel-item");
                    document.getElementById("item_"+i.toString()).classList.add("d-none");
                }

                carousel = document.getElementById("myCarousel");
                new bootstrap.Carousel(carousel);
                
                document.getElementById("upload_image").addEventListener("change", handle_file, false);

                //initialize range
                range_elt = document.getElementById("customRange1");
                range_elt.setAttribute("max", images_list.length-1);

                //make range listen to carousel
                carousel.addEventListener('slid.bs.carousel', function () {
                   range_elt.value = get_active_slide();
                });

                //when modal is closed
                document.getElementById("myModal").addEventListener('hidden.bs.modal', event => {
                    if (!images_list_up_to_date_in_db) {
                        //save images list
                        UploadLibrary(images_list);
                    }

                    //remove selected image indication
                    document.getElementById("select_btn_"+selected_images[current_img_field_id].toString()).classList.remove("d-none");
                    document.getElementById("select_alert_"+selected_images[current_img_field_id].toString()).classList.add("d-none");
                });


                //INITIALIZE FORM

                //website title
                document.getElementById("nameSection1").addEventListener("change", (event) => {
                    document.getElementById("website_title").innerText = document.getElementById("nameSection1").value;
                });

                //hide copy link tooltip when copy button loses focus
                let copy_link_tooltip = new bootstrap.Tooltip(copy_link_btn);
                copy_link_btn.addEventListener("blur", event => {
                    copy_link_tooltip.hide();
                });

                //hide save tooltip when save button loses focus
                let save_tooltip = new bootstrap.Tooltip(save_btn);
                save_btn.addEventListener("blur", event => {
                    save_tooltip.hide();
                });

                //switch prm accessibility
                document.getElementById("switchPrmAccessible").addEventListener("change", (event) => {
                    if (document.getElementById("switchPrmAccessible").checked) {
                        document.getElementById("switchPrmAccessibleLabel").innerText = "Oui";
                    }
                    else {
                        document.getElementById("switchPrmAccessibleLabel").innerText = "Non";
                    }
                });

                //switch email display
                document.getElementById("switchEmailDisplay").addEventListener("change", (event) => {
                    if (document.getElementById("switchEmailDisplay").checked) {
                        document.getElementById("switchEmailDisplayLabel").innerText = "Oui";
                    }
                    else {
                        document.getElementById("switchEmailDisplayLabel").innerText = "Non";
                    }
                });

                //switch phone display
                document.getElementById("switchPhoneDisplay").addEventListener("change", (event) => {
                    if (document.getElementById("switchPhoneDisplay").checked) {
                        document.getElementById("switchPhoneDisplayLabel").innerText = "Oui";
                    }
                    else {
                        document.getElementById("switchPhoneDisplayLabel").innerText = "Non";
                    }
                });

                //save form
                save_btn.addEventListener('click', async event => {
                    //hide any validation CSS style from previous form submit
                    document.querySelectorAll(".was-validated").forEach(elt => {
                        elt.classList.remove("was-validated");
                    });

                    if (document.querySelector('.needs-validation').checkValidity()) {  //form valid
                        save_tooltip.hide();
                        save_btn.disabled = true;
                        const save_btn_width = save_btn.offsetWidth;
                        save_btn.innerHTML = "En cours...";
                        save_btn.style.width = save_btn_width.toString()+"px";
                        update_params();
                        const make_res = await SendRequestToMake(params);
                        save_btn.disabled = false;
                        save_btn.innerHTML = "&nbsp;Publier <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-floppy' viewBox='0 0 16 16' style='position: relative; top: -1px;'><path d='M11 2H9v3h2z'/><path d='M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z'/></svg>&nbsp;";
                        save_btn.style.width = "";
                        if(make_res.ok)
                        {
                            published_modal.show();
                        }
                        else
                        {
                            save_btn.setAttribute("data-bs-original-title", "Erreur : réessayez ou contactez le support technique.");
                            save_tooltip.show();
                        }
                    }
                    else { //form invalid
                        //show invalid tooltip
                        save_btn.setAttribute("data-bs-original-title", "Impossible ! Certains champs sont vides.");
                        save_tooltip.show();

                        //show validation CSS style only on invalid fields
                        for (let i = 1; i < document.querySelectorAll(':invalid').length; i++) {
                            document.querySelectorAll(':invalid')[i].parentElement.classList.add('was-validated');
                        }
                        
                        //go to first section with invalid field
                        go_to_section(parseInt(document.querySelectorAll(':invalid')[1].getAttribute("data-section")));

                        //scroll to first invalid field
                        document.querySelectorAll(':invalid')[1].parentElement.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
                    }
                });

                init_form_values();
                select_theme(current_theme);
            });

            function init_form_values() {
                //PICTURES OF ALL SECTIONS
                
                document.getElementById("welcomePictureSection1").src = images_list[selected_images[0]];
                document.getElementById("welcomePictureSection1_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_gallery_modal_before_opening(0);
                });
                document.getElementById("picture1Section2").src = images_list[selected_images[1]];
                document.getElementById("picture1Section2_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_gallery_modal_before_opening(1);
                });
                document.getElementById("picture2Section2").src = images_list[selected_images[2]];
                document.getElementById("picture2Section2_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_gallery_modal_before_opening(2);
                });
                for (let i = 0; i < params.galerie_images.length; i++) {
                    document.getElementById("picture"+(i+1).toString()+"Section4").src = images_list[selected_images[i+3]];
                    document.getElementById("picture"+(i+1).toString()+"Section4_link").addEventListener("click", (ev) => {
                        ev.preventDefault();
                        set_up_gallery_modal_before_opening(i+3);
                    });
                }
                document.getElementById("contactPictureSection5").src = images_list[selected_images[9]];
                document.getElementById("contactPictureSection5_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_gallery_modal_before_opening(9);
                });


                //SECTION 1

                //website title
                document.getElementById("website_title").innerText = params.nom;

                //website URL
                let url_input = document.getElementById("website_url");
                url_input.value = params.nom_de_domaine.replace(/(^\w+:|^)\/\//, '').replace(/\/$/, '');
                document.getElementById("view_site_btn").href = params.nom_de_domaine;
                document.getElementById("published_modal_link").href = params.nom_de_domaine;
                document.getElementById("published_modal_link").innerText = params.nom_de_domaine.replace(/\/$/, "");

                //copy link button
                copy_link_btn.addEventListener("click", function() {
                    url_input.select();
                    url_input.setSelectionRange(0, 99999);
                    navigator.clipboard.writeText(params.nom_de_domaine);
                });

                document.getElementById("nameSection1").value = params.nom;
                document.getElementById("typeAndLocationSection1").value = params.type_et_localisation;
                document.getElementById("addressSection1").value = params.adresse[0];
                document.getElementById("citySection1").value = params.adresse[1];
                document.getElementById("zipSection1").value = params.adresse[2];
                document.getElementById("welcomeTextSection1").value = params.texte_bienvenue;
                document.getElementById("switchPrmAccessible").checked = params.accessibilite;
                if (params.accessibilite) {
                    document.getElementById("switchPrmAccessibleLabel").innerText = "Oui";
                }


                //SECTION 2

                document.getElementById("qualifier1Section2").value = params.esprit[0].Qual;
                document.getElementById("textQualifier1Section2").value = params.esprit[0].Desc;
                document.getElementById("qualifier2Section2").value = params.esprit[1].Qual;
                document.getElementById("textQualifier2Section2").value = params.esprit[1].Desc;


                //SECTION 3
                
                for (const elt of params.caracteristiques) {
                    add_row(elt);
                }


                // SECTION 5

                document.getElementById("emailSection5").value = params.email;
                document.getElementById("switchEmailDisplay").checked = params.contact_proprio[1].length > 0;
                if (params.contact_proprio[1].length > 0) {
                    document.getElementById("switchEmailDisplayLabel").innerText = "Oui";
                }
                document.getElementById("phoneSection5").value = params.telephone;
                document.getElementById("switchPhoneDisplay").checked = params.contact_proprio[0].length > 0;
                if (params.contact_proprio[0].length > 0) {
                    document.getElementById("switchPhoneDisplayLabel").innerText = "Oui";
                }


                //SECTION 6

                document.getElementById("instaSection6").value = params.contact_proprio[2];
                document.getElementById("fbSection6").value = params.contact_proprio[3];
                document.getElementById("bookingSection6").value = params.liens_plateformes[0];
                document.getElementById("airbnbSection6").value = params.liens_plateformes[1];
                document.getElementById("gitesfrSection6").value = params.liens_plateformes[2];
                document.getElementById("chambreshotesfrSection6").value = params.liens_plateformes[3];


                //COUNTERS OF ALL SECTIONS

                let text_max = 200;
                document.getElementById("countHelpSection1").innerText = document.getElementById("welcomeTextSection1").value.length + " / " + text_max;
                document.getElementById("welcomeTextSection1").addEventListener("input", (event) => {
                    document.getElementById("countHelpSection1").innerText = document.getElementById("welcomeTextSection1").value.length + " / " + text_max;
                });
                document.getElementById("countHelp1Section2").innerText = document.getElementById("textQualifier1Section2").value.length + " / " + text_max;
                document.getElementById("textQualifier1Section2").addEventListener("input", (event) => {
                    document.getElementById("countHelp1Section2").innerText = document.getElementById("textQualifier1Section2").value.length + " / " + text_max;
                });
                document.getElementById("countHelp2Section2").innerText = document.getElementById("textQualifier2Section2").value.length + " / " + text_max;
                document.getElementById("textQualifier2Section2").addEventListener("input", (event) => {
                    document.getElementById("countHelp2Section2").innerText = document.getElementById("textQualifier2Section2").value.length + " / " + text_max;
                });
            };

            function update_params() {
                params.nom = document.getElementById("nameSection1").value;
                params.type_et_localisation = document.getElementById("typeAndLocationSection1").value;
                params.adresse[0] = document.getElementById("addressSection1").value;
                params.adresse[1] = document.getElementById("citySection1").value;
                params.adresse[2] = document.getElementById("zipSection1").value;
                params.texte_bienvenue = document.getElementById("welcomeTextSection1").value;
                params.accessibilite = document.getElementById("switchPrmAccessible").checked;
                params.image_accueil = images_list[selected_images[0]];

                params.esprit[0].Qual = document.getElementById("qualifier1Section2").value;
                params.esprit[0].Desc = document.getElementById("textQualifier1Section2").value;
                params.esprit[0].Img = images_list[selected_images[1]];
                params.esprit[1].Qual = document.getElementById("qualifier2Section2").value;
                params.esprit[1].Desc = document.getElementById("textQualifier2Section2").value;
                params.esprit[1].Img = images_list[selected_images[2]];

                params.caracteristiques = [];
                document.querySelectorAll(".item_input").forEach(elt => {
                    params.caracteristiques.push(elt.value);
                });

                for (let i = 0; i < params.galerie_images.length; i++) {
                    params.galerie_images[i] = images_list[selected_images[i+3]];
                }

                params.email = document.getElementById("emailSection5").value;
                params.telephone = document.getElementById("phoneSection5").value;
                params.contact_image = images_list[selected_images[9]];

                params.contact_proprio[0] = document.getElementById("switchPhoneDisplay").checked ? params.telephone : "";
                params.contact_proprio[1] = document.getElementById("switchEmailDisplay").checked ? params.email : "";
                params.contact_proprio[2] = document.getElementById("instaSection6").value;
                params.contact_proprio[3] = document.getElementById("fbSection6").value;

                params.liens_plateformes[0] = document.getElementById("bookingSection6").value;
                params.liens_plateformes[1] = document.getElementById("airbnbSection6").value;
                params.liens_plateformes[2] = document.getElementById("gitesfrSection6").value;
                params.liens_plateformes[3] = document.getElementById("chambreshotesfrSection6").value;

                params.bibliotheque = images_list;
            };

            const SendRequestToMake = (data) => {
                let cooked_data = Object.assign({}, data);
                cooked_data.esprit = JSON.stringify(data.esprit);
                cooked_data.caracteristiques = JSON.stringify(data.caracteristiques);
                cooked_data.galerie_images = JSON.stringify(data.galerie_images);
                cooked_data.adresse = JSON.stringify(data.adresse);
                cooked_data.contact_proprio = JSON.stringify(data.contact_proprio);
                cooked_data.liens_plateformes = JSON.stringify(data.liens_plateformes);
                cooked_data.bibliotheque = JSON.stringify(data.bibliotheque);

                return fetch(modifWebhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(cooked_data)
                });
            };

            function move_carousel_to(img_nb) {
                new bootstrap.Carousel(carousel).to(img_nb);
            };

            function get_active_slide() {
                for (let i = 0; i < images_list.length; i++) {
                    if (document.getElementById("item_"+i.toString()).classList.contains("active")) {
                        return i;
                    }
                }
            };

            function update_nb_slides_spans() {
                for (let i = 0; i < nb_slides_spans.length; i++) {
                    nb_slides_spans[i].innerText = images_list.length.toString();
                }
            };

            function update_img_on_slides() {
                for (let i = 0; i < images_list.length; i++) {
                    document.getElementById("img_slide_"+i.toString()).src = images_list[i];
                }
            };

            function delete_img(img_nb) {
                images_list.splice(img_nb, 1);
                images_list_up_to_date_in_db = false;
                update_nb_slides_spans();
                range_elt.setAttribute("max", images_list.length-1);
                update_img_on_slides();
                update_selected_images(img_nb); //update selected images whose ids need to change following image deletion

                //if last slide is currently displayed, go to previous one
                if (document.getElementById("item_"+images_list.length.toString()).classList.contains("active")) {
                    new bootstrap.Carousel(carousel).prev();
                }

                //remove last slide
                document.getElementById("img_slide_"+images_list.length.toString()).src = "";
                document.getElementById("item_"+images_list.length.toString()).classList.remove("carousel-item");
                document.getElementById("item_"+images_list.length.toString()).classList.add("d-none");

                //if no image left to display
                if (images_list.length == 0) {
                    document.getElementById("no_slide").classList.remove("d-none");
                }
                //if max images limit not reached anymore
                else if (images_list.length == max_images-1) {
                    upload_tooltip.disable();
                    upload_span.classList.remove("disabled");
                }
            };

            function add_img(img_url) {
                images_list.push(img_url);
                images_list_up_to_date_in_db = false;
                update_nb_slides_spans();
                range_elt.setAttribute("max", images_list.length-1);

                //if 0 to 1 image
                if (images_list.length == 1) {
                    document.getElementById("no_slide").classList.add("d-none");
                }
                //if max images reached
                else if (images_list.length == max_images) {
                    upload_tooltip.enable();
                    upload_span.classList.add("disabled");
                }

                //add slide and go to it
                document.getElementById("img_slide_"+(images_list.length-1).toString()).src = img_url;
                document.getElementById("item_"+(images_list.length-1).toString()).classList.remove("d-none");
                document.getElementById("item_"+(images_list.length-1).toString()).classList.add("carousel-item");
                new bootstrap.Carousel(carousel).to(images_list.length-1);
            };

            const countOccurrences = (arr, val) => arr.reduce((a, v) => (v === val ? a + 1 : a), 0);

            function select_img(img_nb) {
                //show select button on previous selected image and enable delete button if necessary
                document.getElementById("select_btn_"+selected_images[current_img_field_id].toString()).classList.remove("d-none");
                document.getElementById("select_alert_"+selected_images[current_img_field_id].toString()).classList.add("d-none");
                if (countOccurrences(selected_images, selected_images[current_img_field_id]) == 1) {
                    delete_buttons[selected_images[current_img_field_id]].disabled = false;
                    tooltipList[selected_images[current_img_field_id]].disable();
                }
                
                selected_images[current_img_field_id] = img_nb;

                //remove select button on new selected image and disable delete button
                document.getElementById("select_btn_"+selected_images[current_img_field_id].toString()).classList.add("d-none");
                document.getElementById("select_alert_"+selected_images[current_img_field_id].toString()).classList.remove("d-none");
                delete_buttons[selected_images[current_img_field_id]].disabled = true;
                tooltipList[selected_images[current_img_field_id]].enable();

                //apply new image thumbnail
                document.getElementById(img_field_id_to_html_id[current_img_field_id]).src = images_list[img_nb];
            };

            function update_selected_images(deleted_img_nb) {
                //update ids of selected images that are after deleted image
                for (let i = 0; i < selected_images.length; i++) {
                    if (i != current_img_field_id) {
                        if (deleted_img_nb < selected_images[i]) {
                            selected_images[i] = selected_images[i]-1;
                        }
                    }
                }
                if (deleted_img_nb < selected_images[current_img_field_id]) {
                    select_img(selected_images[current_img_field_id]-1);
                }
                
                for (let i = 0; i < delete_buttons.length; i++) {
                    //disable delete button and enable tooltip for selected images
                    if (selected_images.includes(i)) {
                        delete_buttons[i].disabled = true;
                        tooltipList[i].enable();
                    }
                    //enable delete button and disable tooltip for other images
                    else {
                        delete_buttons[i].disabled = false;
                        tooltipList[i].disable();
                    }
                }
            };

            function set_up_gallery_modal_before_opening(img_field_id) {
                current_img_field_id = img_field_id;
                
                //indicate selected image
                document.getElementById("select_btn_"+selected_images[current_img_field_id].toString()).classList.add("d-none");
                document.getElementById("select_alert_"+selected_images[current_img_field_id].toString()).classList.remove("d-none");

                //put carousel focus on selected image
                new bootstrap.Carousel(carousel).to(selected_images[current_img_field_id]);
                range_elt.value = selected_images[current_img_field_id];
            };

            const UploadFile = (file) => {
                console.log('start file upload : ', file.name);
                const formData = new FormData();
                formData.append('file', file);

                const authHeader = 'Basic ' + btoa(`${username}:${applicationPassword}`);

                const response = fetch(wordpressApiUrl, {
                    method: 'POST',
                    credentials: 'omit',
                    headers: {
                        'Authorization': authHeader
                    },
                    body: formData
                });

                return response;    
            };

            const UrlFromUploadResponse = (response) => {
                if(response)
                {
                    if (response.ok)
                    {
                        return response.json()
                        .then((data)=>{return data.source_url})
                        .catch((err)=>{return ""});
                    }
                    response.json().then((err) => {console.err(err.message)});
                }
                return "";
            };

            //PENSER A AJOUTER UN CHECK AFIN DE NE PAS UPLOAD N'IMPORTE QUEL FICHIER (MEME SI CE NE SERA PAS UNE PROTECTION FIABLE COMME ON EST EN FRONT)
            async function handle_file() {

                //grey slide + show loading icon + disable buttons
                if (images_list.length > 0) {
                    document.getElementById("carousel_inner").style.webkitFilter = "brightness(50%)"; /* Safari 6.0 - 9.0 */
                    document.getElementById("carousel_inner").style.filter = "brightness(50%)";

                    document.getElementById("loading_image_icon").style.visibility = "visible";
                    
                    for (let i = 0; i < images_list.length; i++) {
                        delete_buttons[i].disabled = true;
                        select_buttons[i].disabled = true;
                    }

                    document.getElementById("carousel-control-prev").disabled = true;
                    document.getElementById("carousel-control-next").disabled = true;
                    document.getElementById("customRange1").disabled = true;
                }
                else {
                    document.getElementById("no_slide").innerHTML = "<span class='spinner-border spinner-border-sm' aria-hidden='true'></span> <span role='status'>Chargement...</span>";
                }
                upload_span.classList.add("disabled");

                //upload file
                try{
                    let res = UploadFile(this.files[0]);
                    res = await res;
                    let url = UrlFromUploadResponse(res);
                    url = await url;
                    console.log(url);
                    add_img(url);
                }
                catch(err){
                    console.error('Error uploading file:', err);
                }
                
                //ungrey slide + hide loading icon + enable buttons
                if (images_list.length > 0) {
                    document.getElementById("carousel_inner").style.webkitFilter = "none"; /* Safari 6.0 - 9.0 */
                    document.getElementById("carousel_inner").style.filter = "none";
                    
                    document.getElementById("loading_image_icon").style.visibility = "hidden";

                    for (let i = 0; i < images_list.length; i++) {
                        if (!selected_images.includes(i)) {
                            delete_buttons[i].disabled = false;
                        }
                        select_buttons[i].disabled = false;
                    }

                    document.getElementById("carousel-control-prev").disabled = false;
                    document.getElementById("carousel-control-next").disabled = false;
                    document.getElementById("customRange1").disabled = false;
                }
                else {
                    document.getElementById("no_slide").innerHTML = "Aucune image à afficher.";
                }
                if (images_list.length < max_images) {
                    upload_span.classList.remove("disabled");
                }
            };

            const UploadLibrary = (library) => {
                const data = {
                        code: params.code,
                        library: JSON.stringify(library)
                    };
                return fetch(libraryWebhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (response.ok) {  // Check if the response was successful
                        images_list_up_to_date_in_db = true;
                    }
                });
            };

            function go_to_section(nb, ev=-1) {
                if (ev != -1) {
                    ev.preventDefault();
                }
                
                document.getElementById("section"+current_section.toString()).classList.add("d-none");
                document.getElementById("link_sect_"+current_section.toString()).classList.remove("active");
                document.getElementById("section"+nb.toString()).classList.remove("d-none");
                document.getElementById("link_sect_"+nb.toString()).classList.add("active");
                current_section = nb;
            };

            function add_row(val) {
                if (nb_fields_value < max_fields) {
                    // Create a new row with input field and delete button
                    const newRow = document.createElement('div');
                    newRow.classList.add('row', 'mb-3');
                    newRow.innerHTML = `
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" class="form-control item_input" value="`+val+`" placeholder="ex : 3 chambres avec lits doubles" data-section="3" required>
                                <button onclick="delete_row(event)" class="btn btn-danger" type="button"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    `;

                    // Append the new row to the container
                    document.getElementById('newinput').appendChild(newRow);

                    nb_fields_value += 1;
                    nb_fields_span.innerText = nb_fields_value.toString();

                    if (nb_fields_value == max_fields) {
                        document.getElementById("add_row_btn").disabled = true;
                    }
                }
            };

            function delete_row(ev) {
                // Find the row and remove it from the DOM
                const rowToDelete = ev.target.closest('.row');
                rowToDelete.remove();
                nb_fields_value -= 1;
                nb_fields_span.innerText = nb_fields_value.toString();
                document.getElementById("add_row_btn").disabled = false;
            };

            function select_theme(theme_nb, ev=-1) {
                if (ev != -1) {
                    ev.preventDefault();
                }

                document.getElementById("thumbnail_theme_"+current_theme.toString()).classList.remove("border", "border-primary", "border-3", "rounded-3");
                document.querySelector("#thumbnail_theme_"+current_theme.toString()+" .badge").classList.add("d-none");
                current_theme = theme_nb;
                document.getElementById("thumbnail_theme_"+current_theme.toString()).classList.add("border", "border-primary", "border-3", "rounded-3");
                document.querySelector("#thumbnail_theme_"+current_theme.toString()+" .badge").classList.remove("d-none");
                document.getElementById("current_theme").textContent = themes_names_and_images[theme_nb][0];
            };

            function set_up_theme_modal_before_opening(theme_nb) {
                previewed_theme = theme_nb;
                document.getElementById("theme_name").textContent = themes_names_and_images[theme_nb][0];
                document.getElementById("theme_img").src = themes_names_and_images[theme_nb][1];
            };
        </script>
    </head>
    <body class="d-flex flex-column h-100">
        
        <div class="container">

            <div class="row my-3">
                <div class="col">
                    <!--<h1>
                        <svg style="position: relative; top: -4px;" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(0.12851406,0,0,0.12812962,-0.06425703,-0.01027692)">
                                <path
                                    style="fill:#0d6efd;"
                                    d="M 44.225313,248.9311 C 26.075466,246.57282 8.6035971,231.25612 2.7154611,212.54153 0.50484204,205.51539 0.5,205.32406 0.5,125 0.5,44.675938 0.50484204,44.48461 2.7154611,37.458475 8.7609441,18.243774 25.905799,3.51422 45.130828,1.0184198 c 9.636009,-1.25095009 150.102332,-1.25095009 159.738342,0 C 224.0942,3.51422 241.23906,18.243774 247.28454,37.458475 249.49516,44.48461 249.5,44.675938 249.5,125 c 0,80.32406 -0.005,80.51539 -2.21546,87.54153 -6.04548,19.2147 -23.19034,33.94425 -42.41537,36.44005 -8.92002,1.158 -151.688391,1.11313 -160.643857,-0.0505 z M 206.6446,138.28345 c 10.59648,-5.66705 10.36056,-20.57227 -0.41036,-25.92699 C 202.74182,110.62022 199.16505,110.5 151,110.5 c -51.263932,0 -51.518998,0.0102 -55.644603,2.21655 -2.875407,1.53779 -4.789336,3.5484 -6.25,6.56573 -2.618642,5.40939 -2.62596,7.01093 -0.05629,12.31916 2.307712,4.7671 5.178779,7.00502 10.684297,8.32812 2.071626,0.49785 26.041596,0.82987 53.266596,0.73782 49.04124,-0.16583 49.53841,-0.18792 53.6446,-2.38393 z m 0,-68.000004 C 217.24108,64.616399 217.00516,49.711184 206.23424,44.356462 202.89612,42.696931 199.84803,42.5 177.5,42.5 c -23.90093,0 -25.18221,0.09745 -29.1446,2.216554 -2.87541,1.537782 -4.78934,3.548395 -6.25,6.565725 -2.61864,5.409392 -2.62596,7.010933 -0.0563,12.319163 4.07944,8.427002 7.74743,9.316398 37.45089,9.080905 21.64724,-0.171622 23.24377,-0.312715 27.1446,-2.398901 z m 0,137.000004 c 10.59648,-5.66705 10.36056,-20.57227 -0.41036,-25.92699 C 202.74182,179.62022 199.16505,179.5 151,179.5 c -51.26393,0 -105.519,0.0102 -109.6446,2.21655 -2.87541,1.53779 -4.789339,3.5484 -6.250003,6.56573 -2.618642,5.40939 -2.62596,7.01093 -0.05629,12.31916 2.307712,4.7671 5.178783,7.00502 10.684293,8.32812 2.07163,0.49785 80.0416,0.82987 107.2666,0.73782 49.04124,-0.16583 49.53841,-0.18792 53.6446,-2.38393 z"
                                    id="path2" />
                            </g>
                        </svg> Mon site
                    </h1>-->
                    <h1>
                        <svg style="position: relative; top: -4px; border-radius: 5px;" version="1.1"
   id="svg1"
   width="151"
   height="46"
   viewBox="0 0 151 46"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:svg="http://www.w3.org/2000/svg">
  <defs
     id="defs1" />
  <g
     id="g1"
     transform="translate(-75.5,-23)">
    <g
       id="g2"
       transform="matrix(0.5,0,0,0.5,75.5,23)">
      <path
         style="fill:#0d6efd;fill-opacity:1"
         d="M 0,46 V 0 H 151 302 V 46 92 H 151 0 Z m 191.5,22.901255 c 10.7636,-2.926599 15.66076,-15.291731 10.25,-25.880823 -3.19718,-6.257026 -6.52563,-8.350274 -14.01077,-8.811329 -5.2832,-0.325424 -6.76144,-0.02612 -9.64728,1.953293 -5.47284,3.753856 -7.41384,7.139569 -7.89333,13.768481 -0.71039,9.820971 3.99366,17.152545 12.28046,19.139912 4.3263,1.037548 4.6004,1.032396 9.02092,-0.169534 z m -8.88929,-9.433401 c -1.94758,-1.947579 -3.03497,-7.636736 -2.14286,-11.211281 1.63174,-6.538099 7.98972,-8.541949 11.2591,-3.548539 2.37591,3.628794 2.26252,11.541132 -0.20445,14.267108 -2.16882,2.396514 -6.75446,2.650043 -8.91179,0.492712 z m 90.88589,7.615297 c 11.2588,-6.35447 10.56649,-26.029081 -1.11089,-31.570363 -4.93897,-2.343694 -13.88711,-1.716073 -18.07782,1.267975 -8.61605,6.135165 -8.72354,23.271354 -0.18509,29.504988 5.28063,3.855206 13.36636,4.188004 19.3738,0.7974 z m -13.49705,-7.583698 c -1.86094,-2.242293 -2.43835,-10.025882 -1.00115,-13.495601 2.02703,-4.893664 8.43795,-5.489163 10.71174,-0.994994 1.75165,3.462139 1.60365,11.098125 -0.26684,13.768636 -1.84134,2.628872 -7.50201,3.06162 -9.44375,0.721959 z M 69.171259,67.345012 c 2.242545,-2.029475 2.317589,-3.713307 0.257312,-5.773583 C 68.094259,60.237116 64.50303,60 45.628571,60 29.673016,60 23.061354,60.338646 22.2,61.2 c -1.768352,1.768352 -1.458849,5.549854 0.55,6.719901 1.035979,0.603403 10.490131,1.031689 23.171259,1.04969 18.557326,0.02634 21.665755,-0.190856 23.25,-1.624579 z M 98.880638,63.75 100.35114,59 h 8.14886 8.14886 l 1.4705,4.75 1.4705,4.75 5.20507,0.30226 C 128.41762,69.01263 130,68.729657 130,67.871452 130,66.882099 118.10355,31.597523 115.4613,24.75 114.91705,23.339557 113.56162,23 108.47572,23 h -6.3103 L 100.595,27.25 C 96.068779,39.499245 87,66.483055 87,67.701404 c 0,1.063156 1.261132,1.329881 5.205071,1.100856 L 97.410142,68.5 Z M 104,49.357813 C 104,48.177477 108.07953,35 108.44494,35 108.80347,35 113,48.271422 113,49.405237 113,49.732357 110.975,50 108.5,50 106.025,50 104,49.711016 104,49.357813 Z m 41,14.723974 C 145,58.940925 145.70713,56 146.94323,56 c 0.40109,0 2.71545,2.91429 5.14301,6.4762 l 4.41376,6.4762 5.80902,0.0238 c 4.37758,0.01793 5.62768,-0.284221 5.07315,-1.2262 -0.40473,-0.6875 -3.01879,-4.609399 -5.80902,-8.715332 -2.79023,-4.105932 -5.41551,-8.012164 -5.83395,-8.680514 -0.47391,-0.756947 1.50582,-3.842202 5.25,-8.181706 C 164.29514,38.340859 167,34.934591 167,34.602962 167,34.271333 164.55217,34 161.56038,34 h -5.43961 L 150.81038,40.457199 145.5,46.914398 145.21922,34.957199 144.93844,23 H 139.96922 135 v 23 23 h 5 5 z m 74.82546,2.861001 0.76101,-2.057212 1.86175,2.057212 c 2.28266,2.522304 9.22192,2.79627 13.59163,0.536605 6.13472,-3.172389 9.11531,-16.385616 5.59612,-24.808203 C 238.3236,34.743552 228.612,31.370959 222.63486,36.07258 L 220,38.14516 V 30.57258 23 h -5 -5 v 23 23 h 4.53222 c 3.66096,0 4.67852,-0.395476 5.29324,-2.057212 z m 1.73124,-8.165294 C 219.21278,55.431079 219.4801,46.519898 222,44 c 4.5677,-4.567698 9.99272,-1.164559 10.74417,6.739869 0.36292,3.817476 0.0226,5.304717 -1.7184,7.510131 -2.74552,3.477851 -7.22767,3.727539 -9.46907,0.527494 z M 69.171259,49.345012 c 2.269186,-2.053585 2.324803,-4.139388 0.173753,-6.516271 C 67.886478,41.217078 65.966889,41 53.173753,41 41.069534,41 38.353614,41.274996 36.828741,42.654988 34.559555,44.708573 34.503938,46.794376 36.654988,49.171259 38.113522,50.782922 40.033111,51 52.826247,51 64.930466,51 67.646386,50.725004 69.171259,49.345012 Z m 1.008844,-19.84095 c 0.942433,-2.068416 0.820774,-2.845611 -0.710265,-4.537391 -1.614233,-1.783707 -2.850335,-2.005169 -9.790394,-1.754062 -6.708156,0.242715 -8.153549,0.605507 -9.297946,2.33377 -1.154191,1.743053 -1.126261,2.373011 0.188433,4.25 C 51.954242,31.772759 52.986304,32 60.578143,32 c 8.054956,0 8.519788,-0.120829 9.60196,-2.495938 z"
         id="path2" />
    </g>
  </g>
</svg> Mon site
                    </h1>
                </div>
                <!--<div class="col mt-auto">
                    <p id="any_questions" class="float-end" style="position: relative; bottom: -3px;"><u>Des questions ?</u></p>
                </div>-->
            </div>

            <div class="row">
                <h4 id="website_title"></h4>
            </div>

            <div class="row mb-3">
                <div class="col-sm mb-4">
                    <div class="input-group">
                        <input class="form-control" type="text" id="website_url" value="" aria-describedby="copy_link_btn" disabled readonly>
                        <button class="btn btn-secondary" type="button" id="copy_link_btn" data-bs-toggle="tooltip" data-bs-title="Lien copié !" data-bs-trigger="click">Copier le lien <i class="bi bi-clipboard" style="position: relative; top: -1px;"></i></button>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="float-sm-end">
                        <a id="view_site_btn" href="#" target="_blank" class="btn btn-secondary" role="button">Voir site <i class="bi bi-box-arrow-up-right" style="position: relative; top: -2px;"></i></a>
                        <button type="button" id="save_btn" class="btn btn-primary" data-bs-toggle="tooltip" title="Impossible ! Certains champs sont vides." data-bs-trigger="manual">&nbsp;Publier <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16" style="position: relative; top: -1px;">
                                                                                <path d="M11 2H9v3h2z"/>
                                                                                <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                                                                            </svg>&nbsp;
                        </button>
                    </div>
                </div>              
            </div>
            
            <div class="card text-bg-light mb-3">
                <div class="card-header">
                    <ul class="nav nav-pills card-header-pills">
                        <li class="nav-item">
                            <a id="link_sect_1" onclick="go_to_section(1, event)" class="nav-link active" aria-current="true" href="#">Présentation du logement</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_2" onclick="go_to_section(2, event)" class="nav-link" href="#">Description</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_3" onclick="go_to_section(3, event)" class="nav-link" href="#">Équipements</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_4" onclick="go_to_section(4, event)" class="nav-link" href="#">Galerie photos</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_5" onclick="go_to_section(5, event)" class="nav-link" href="#">Contact propriétaire</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_6" onclick="go_to_section(6, event)" class="nav-link" href="#">Liens externes</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_7" onclick="go_to_section(7, event)" class="nav-link" href="#">Apparence</a>
                        </li>
                        <li class="nav-item">
                            <a id="link_sect_8" onclick="go_to_section(8, event)" class="nav-link" href="#">Abonnement</a>
                        </li>
                    </ul>
                </div>

                <form class="needs-validation" novalidate>
                    <!--Section 1 : Présentation du logement-->
                    <div id="section1" class="card-body">

                        <div class="mb-3">
                            <label for="nameSection1" class="form-label">Nom</label>
                            <input class="form-control" type="text" id="nameSection1" placeholder="ex : Villa Florentine" data-section="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="typeAndLocationSection1" class="form-label">Type de logement et localisation</label>
                            <input class="form-control" type="text" id="typeAndLocationSection1" placeholder="ex : Villa à Aix-les-Bains" data-section="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="welcomeTextSection1" class="form-label">Texte de bienvenue</label>
                            <textarea class="form-control" id="welcomeTextSection1" name="text" maxlength="200" 
                            placeholder="ex : Nous vous accueillons pour vos séjours à Aix-les-Bains dans cette villa située à proximité de l'Esplanade du Lac." 
                            rows="4" aria-describedby="countHelpSection1" data-section="1" required></textarea>
                            <div id="countHelpSection1" class="form-text"></div>
                        </div>

                        <div class="mb-3">
                            <label for="addressSection1" class="form-label">Adresse (facultatif)</label>

                            <div>
                                <div id="addressHelp" class="form-text">N° et rue</div>
                                <input class="form-control" type="text" id="addressSection1" placeholder="ex : 6 Rue des Acacias" aria-describedby="addressHelp" data-section="1">
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div id="cityHelp" class="form-text">Ville</div>
                                    <input class="form-control" type="text" id="citySection1" placeholder="ex : Aix-les-Bains" aria-describedby="cityHelp" data-section="1">
                                </div>
                                <div class="col-md-4">
                                    <div id="zipHelp" class="form-text">Code postal</div>
                                    <input class="form-control" type="text" id="zipSection1" placeholder="ex : 73100" aria-describedby="zipHelp" data-section="1">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="prmAccessibilitySection1" class="form-label">Établissement accessible aux personnes à mobilité réduite :</label>
                            <div class="form-check form-switch" id="prmAccessibilitySection1">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchPrmAccessible">
                                <label class="form-check-label" for="switchPrmAccessible" id="switchPrmAccessibleLabel">Non</label>
                            </div>  
                        </div>

                        <div class="mb-3">
                            <label for="welcomePictureSection1" class="form-label">Photo d'accueil</label>
                            <div class="card picture_card" style="width: 18rem; height: 12rem;">
                                <img id="welcomePictureSection1" src="" class="card-img" alt="..." style="width: 18rem; height: 12rem; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="welcomePictureSection1_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>
                        
                    </div>

                    <!--Section 2 : Description-->
                    <div id="section2" class="card-body d-none">

                        <div class="mb-3">
                            <div class="alert alert-primary" role="alert">
                                <h4 class="alert-heading">Pour valoriser votre logement :</h4>
                                <ul class="mb-0">
                                    <li>Choisissez 2 adjectifs qui décrivent le mieux votre logement (ex : calme, spacieux).</li>
                                    <li>Pour chaque adjectif, développez avec un court texte et illustrez-le par une photo de votre logement.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5>Premier adjectif</h5>
                        </div>

                        <div class="mb-3">
                            <label for="qualifier1Section2" class="form-label">Votre logement est :</label>
                            <input class="form-control" type="text" id="qualifier1Section2" placeholder="ex : Moderne" data-section="2" required>              
                        </div>

                        <div class="mb-3">
                            <label for="textQualifier1Section2" class="form-label">Développez en 2-3 phrases max. :</label>
                            <textarea class="form-control" id="textQualifier1Section2" name="text" maxlength="200" 
                            placeholder="ex : La Villa Florentine a été entièrement rénovée l'année dernière. Elle possède les équipements les plus récents dans chacune de ses pièces." 
                            rows="4" aria-describedby="countHelp1Section2" data-section="2" required></textarea>
                            <div id="countHelp1Section2" class="form-text"></div>
                        </div>

                        <div class="mb-3">
                            <label for="picture1Section2" class="form-label">Photo d'illustration</label>
                            <div class="card picture_card" style="width: 18rem; height: 12rem;">
                                <img id="picture1Section2" src="" class="card-img" alt="..." style="width: 18rem; height: 12rem; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="picture1Section2_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <hr class="bg-dark border-top border-dark" />
                        </div>

                        <div class="mb-3">
                            <h5>Second adjectif</h5>
                        </div>

                        <div class="mb-3">
                            <label for="qualifier2Section2" class="form-label">Votre logement est :</label>
                            <input class="form-control" type="text" id="qualifier2Section2" placeholder="ex : Convivial" data-section="2" required>
                        </div>

                        <div class="mb-3">
                            <label for="textQualifier2Section2" class="form-label">Développez en 2-3 phrases max. :</label>
                            <textarea class="form-control" id="textQualifier2Section2" name="text" maxlength="200" 
                            placeholder="ex : La Villa Florentine offre des espaces ouverts et lumineux, créant ainsi une atmosphère chaleureuse et propice aux moments de partage." 
                            rows="4" aria-describedby="countHelp2Section2" data-section="2" required></textarea>
                            <div id="countHelp2Section2" class="form-text"></div>
                        </div>

                        <div class="mb-3">
                            <label for="picture2Section2" class="form-label">Photo d'illustration</label>
                            <div class="card picture_card" style="width: 18rem; height: 12rem;">
                                <img id="picture2Section2" src="" class="card-img" alt="..." style="width: 18rem; height: 12rem; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="picture2Section2_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>

                    </div>

                    <!--Section 3 : Équipements-->
                    <div id="section3" class="card-body d-none">

                        <div class="mb-3">
                            <p>Indiquez les équipements de votre logement (WiFi, parking, clim, etc). Maximum 12 éléments.</p>
                        </div>
                            
                        <div id="newinput"></div>

                        <button onclick="add_row('')" type="button" id="add_row_btn" class="btn btn-dark">
                            <span class="bi bi-plus-square" style="position: relative; top: -2px;"></span> Ajouter (<span id="nb_fields"></span>/12)
                        </button>

                    </div>

                    <!--Section 4 : Galerie photos-->
                    <div id="section4" class="card-body d-none">

                        <div class="mb-3">
                            <p>Offrez un aperçu de votre logement avec 6 photos.</p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-md-3 g-5 p-4">
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture1Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture1Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture2Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture2Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture3Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture3Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture4Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture4Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture5Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture5Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card picture_card">
                                            <img id="picture6Section4" src="" class="card-img gallery_img" alt="..." style="aspect-ratio: 1.5 / 1; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                            <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture6Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!--Section 5 : Contact propriétaire-->
                    <div id="section5" class="card-body d-none">

                        <div class="mb-3">
                            <div class="alert alert-primary" role="alert">
                                <div class="col">
                                    <i class="bi bi-info-circle-fill" style="position: relative; top: -2px;"></i> Les demandes de réservation et questions posées via le formulaire de votre site vous sont transférées par e-mail.<br>
                                    Assurez-vous qu'elles n'atterrissent pas dans vos spams.
                                </div>
                            </div>                              
                        </div>

                        <div class="mb-3">
                            <label for="emailSection5" class="form-label">E-mail</label>
                            <input class="form-control" type="text" id="emailSection5" placeholder="ex : villa-florentine@mail.com" data-section="5" required>
                        </div>

                        <div class="mb-3">
                            <label for="emailDisplaySection5" class="form-label">Faire figurer l'e-mail sur le site :</label>
                            <div class="form-check form-switch" id="emailDisplaySection5">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchEmailDisplay">
                                <label class="form-check-label" for="switchEmailDisplay" id="switchEmailDisplayLabel">Non</label>
                            </div>  
                        </div>

                        <div class="mb-3">
                            <label for="phoneSection5" class="form-label">Téléphone</label>
                            <input class="form-control" type="text" id="phoneSection5" placeholder="ex : +33 (0)6 09 09 09 09" data-section="5" required>
                        </div>

                        <div class="mb-3">
                            <label for="phoneDisplaySection5" class="form-label">Faire figurer le numéro sur le site :</label>
                            <div class="form-check form-switch" id="phoneDisplaySection5">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchPhoneDisplay">
                                <label class="form-check-label" for="switchPhoneDisplay" id="switchPhoneDisplayLabel">Non</label>
                            </div>  
                        </div>

                        <div class="mb-3">
                            <label for="contactPictureSection5" class="form-label">Photo de la section "Nous contacter"</label>
                            <div class="card picture_card" style="width: 18rem; height: 12rem;">
                                <img id="contactPictureSection5" src="" class="card-img" alt="..." style="width: 18rem; height: 12rem; object-fit: contain; background-color: rgba(0, 0, 0, 0.8);">
                                <div class="card-img-overlay d-flex" style="visibility: hidden;">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="contactPictureSection5_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>
                        
                    </div>

                    <!--Section 6 : Liens externes-->
                    <div id="section6" class="card-body d-none">

                        <div class="mb-3">
                            <p>Indiquez les divers liens que vous souhaitez partager sur votre site.</p>
                        </div>

                        <div class="mb-3">
                            <h5>Réseaux sociaux</h5>
                        </div>

                        <div class="mb-3">
                            <label for="instaSection6" class="form-label">Instagram
                                <svg style="position: relative; top: -1px; left: -3px;" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" width="20px" height="20px">    <path d="M 8 3 C 5.239 3 3 5.239 3 8 L 3 16 C 3 18.761 5.239 21 8 21 L 16 21 C 18.761 21 21 18.761 21 16 L 21 8 C 21 5.239 18.761 3 16 3 L 8 3 z M 18 5 C 18.552 5 19 5.448 19 6 C 19 6.552 18.552 7 18 7 C 17.448 7 17 6.552 17 6 C 17 5.448 17.448 5 18 5 z M 12 7 C 14.761 7 17 9.239 17 12 C 17 14.761 14.761 17 12 17 C 9.239 17 7 14.761 7 12 C 7 9.239 9.239 7 12 7 z M 12 9 A 3 3 0 0 0 9 12 A 3 3 0 0 0 12 15 A 3 3 0 0 0 15 12 A 3 3 0 0 0 12 9 z"/></svg>
                            </label>
                            <input class="form-control" type="text" id="instaSection6" placeholder="ex : instagram.com/villa.florentine">
                        </div>

                        <div class="mb-3">
                            <label for="fbSection6" class="form-label">Facebook
                                <svg style="position: relative; top: -1px;" clip-rule="evenodd" fill-rule="evenodd" height="15.651611" stroke-linejoin="round" stroke-miterlimit="1.414" viewBox="-0.092 0.015 17.4856 17.104721" width="16" version="1.1" id="svg4" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><defs id="defs4" /><path d="m 17.393598,3.2872139 c 0,-1.8121 -1.4719,-3.28330009 -3.284,-3.28330009 H 3.1918979 c -1.812,0 -3.28390005,1.47120009 -3.28390005,3.28330009 V 13.821413 c 0,1.8116 1.47190005,3.2833 3.28400005,3.2833 H 14.109598 c 1.8121,0 3.284,-1.4717 3.284,-3.2833 z" fill="#000000" id="path1" style="stroke-width:0.0064" /><path d="m 11.873498,17.108713 v -6.7713 h 2.2729 l 0.3403,-2.6389991 h -2.6132 v -1.6849 c 0,-0.764 0.2121,-1.2847 1.3078,-1.2847 l 1.3975,-6e-4 v -2.3603 c -0.2418,-0.032 -1.0713,-0.1039 -2.0363,-0.1039 -2.0148,0 -3.3941001,1.2297 -3.3941001,3.4883 v 1.9461 h -2.2787 v 2.6389991 h 2.2787 v 6.7713 z" style="fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.0655154" id="path4684" /></svg>
                            </label>
                            <input class="form-control" type="text" id="fbSection6" placeholder="ex : facebook.com/villa.florentine">
                        </div>

                        <div class="mt-4">
                            <hr class="bg-dark border-top border-dark" />
                        </div>

                        <div class="mb-3">
                            <h5>Plateformes de location</h5>
                        </div>

                        <div class="mb-3">
                            <label for="bookingSection6" class="form-label">Booking.com
                                <svg style="position: relative; top: -1px;" clip-rule="evenodd" fill-rule="evenodd" height="15.656182" stroke-linejoin="round" stroke-miterlimit="1.414" viewBox="-0.092 0.015 17.4856 17.109716" width="16" version="1.1" id="svg4" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><defs id="defs4" /><path d="m 17.3936,3.2982289 c 0,-1.8121 -1.4719,-3.28330005 -3.284,-3.28330005 H 3.1919002 c -1.812,0 -3.28445982,1.47120015 -3.28390004,3.28330005 l 0.0042711,13.8262421 c 6.34850364,0.0023 10.10856774,-0.0087 14.19732874,-0.0087 1.8121,0 3.284,-1.4717 3.284,-3.2833 z" fill="#000000" id="path1" style="stroke-width:0.0064" /><g fill-rule="nonzero" id="g4" transform="matrix(0.00647428,0,0,0.00675032,-0.46135902,-0.4280738)"><path d="m 1241.6,1768.638 -220.052,-0.22 v -263.12 c 0,-56.22 21.808,-85.48 69.917,-92.165 h 150.136 c 107.068,0 176.328,67.507 176.328,176.766 0,112.219 -67.507,178.63 -176.328,178.739 z m -220.052,-709.694 v -69.26 c 0,-60.602 25.643,-89.424 81.862,-93.15 h 112.657 c 96.547,0 154.41,57.753 154.41,154.52 0,73.643 -39.671,159.67 -150.903,159.67 h -198.026 z m 501.037,262.574 -39.78,-22.356 34.74,-29.699 c 40.437,-34.74 108.163,-112.876 108.163,-247.67 0,-206.464 -160.109,-339.614 -407.888,-339.614 H 935.082 v -0.11 h -32.219 c -73.424,2.74 -132.273,62.466 -133.04,136.329 v 1171.499 h 453.586 c 275.396,0 453.148,-149.917 453.148,-382.135 0,-125.04 -57.424,-231.889 -153.972,-286.244" fill="#ffffff" id="path3" /><path d="m 1794.688,1828.066 c 0,-89.492 72.178,-161.894 161.107,-161.894 89.154,0 161.669,72.402 161.669,161.894 0,89.379 -72.515,161.894 -161.67,161.894 -88.928,0 -161.106,-72.515 -161.106,-161.894" fill="#ffffff" id="path4" /></g><rect style="fill:none;stroke-width:1.09285;paint-order:stroke fill markers" id="rect1" width="4.9263554" height="5.0229511" x="-0.84061056" y="12.67991" /><rect style="fill:none;stroke-width:1.09285;paint-order:stroke fill markers" id="rect2" width="4.6365695" height="4.8056116" x="-0.91305691" y="12.752356" /></svg>
                            </label>
                            <input class="form-control" type="text" id="bookingSection6" placeholder="ex : booking.com/hotel/fr/villa.florentine">
                        </div>

                        <div class="mb-3">
                            <label for="airbnbSection6" class="form-label">Airbnb
                                <svg style="position: relative; top: -2px;" viewBox="0 0 100 100" width="16" height="16" version="1.1" id="svg12" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><defs id="defs12" /><circle cx="50" cy="50" r="50" fill="#000000" id="circle3" style="stroke-width:1.35135" /><path fill="#ffffff" d="m 77.309794,59.78024 -0.0047,-0.01297 C 76.212124,56.279722 63.719198,31.918753 59.920387,24.542787 c -1.907659,-3.738681 -5.708828,-6.061356 -9.920298,-6.061356 -4.211469,0 -8.011459,2.322675 -9.91676,6.053103 -3.802349,7.384219 -16.295274,31.745188 -17.392944,35.245706 -0.394973,1.283956 -0.815884,2.799 -0.815884,4.529806 0,7.235663 5.898651,13.122523 13.148462,13.122523 6.564799,0 11.942322,-4.758536 14.977126,-7.983163 3.034805,3.224627 8.412328,7.983163 14.975948,7.983163 7.249811,0 13.148462,-5.88686 13.148462,-13.122523 0.0012,-1.730806 -0.419733,-3.24585 -0.814705,-4.529806 z M 49.99891,61.632485 c -3.122052,-4.144265 -4.911809,-8.151763 -4.911809,-10.999103 0,-2.829655 2.203594,-5.132286 4.912988,-5.132286 2.709395,0 4.912988,2.302631 4.912988,5.132286 0,2.840266 -1.790935,6.847764 -4.914167,10.999103 z m 14.977127,10.887096 c -4.984909,0 -9.376769,-4.276315 -11.803197,-6.938549 l 0.727457,-0.96444 c 2.703499,-3.585408 5.924589,-8.921666 5.924589,-13.98321 0,-5.539049 -4.407187,-10.045274 -9.824797,-10.045274 -5.417609,0 -9.824796,4.506225 -9.824796,10.045274 0,5.072156 3.22109,10.406055 5.92341,13.987926 l 0.726278,0.962082 c -2.426429,2.662234 -6.818289,6.936191 -11.802018,6.936191 -4.540417,0 -8.235474,-3.682088 -8.235474,-8.209535 0,-1.04933 0.295935,-2.101019 0.595406,-3.073712 0.618987,-1.974864 7.638889,-16.142001 17.073429,-34.460478 1.081164,-2.117525 3.152707,-3.382616 5.544944,-3.382616 2.392238,0 4.463781,1.263912 5.548482,3.390869 9.421571,18.291359 16.434398,32.447886 17.067533,34.44515 0.30183,0.984484 0.597765,2.032636 0.597765,3.079608 -0.0024,4.527447 -3.697416,8.210714 -8.239011,8.210714 z" id="path11" style="stroke-width:1.17902" /><path fill="#ffffff" d="m 64.548717,77.393469 c -4.843845,0 -9.734652,-2.488928 -14.547572,-7.399206 -4.81292,4.911424 -9.704871,7.399206 -14.549862,7.399206 -7.48511,0 -13.576283,-6.078573 -13.576283,-13.549939 0,-1.789096 0.42723,-3.330788 0.828115,-4.635385 C 23.758018,55.84185 34.908369,34.0497 39.654856,24.8362 41.642104,20.941881 45.606292,18.520531 50,18.520531 c 4.392563,0 8.359041,2.423641 10.35087,6.325977 4.744197,9.21121 15.893403,31.001069 16.940288,34.344457 l 0.0057,0.01833 c 0.400885,1.304597 0.828115,2.846289 0.828115,4.635385 0,7.47022 -6.091173,13.548793 -13.576283,13.548793 z m -14.547572,-9.727779 0.584148,0.6208 c 4.685782,4.979001 9.383018,7.502291 13.963424,7.502291 6.602016,0 11.971595,-5.35927 11.971595,-11.946396 0,-1.563455 -0.388287,-2.964264 -0.753666,-4.155467 C 74.707162,56.325204 62.609575,32.732504 58.924863,25.577266 57.206781,22.212116 53.787798,20.124074 50,20.124074 c -3.787798,0 -7.20449,2.085751 -8.919136,5.442883 -3.689294,7.162111 -15.801771,30.7823 -16.846365,34.115379 -0.364233,1.188913 -0.754811,2.593158 -0.754811,4.160049 0,6.587126 5.369579,11.946396 11.971595,11.946396 4.581551,0 9.281078,-2.524435 13.965715,-7.502291 z m 14.547572,4.954948 c -3.819868,0 -7.763439,-2.290776 -12.058644,-7.001757 l -0.447846,-0.491371 1.10759,-1.468387 c 2.551924,-3.385767 5.592929,-8.408293 5.592929,-13.102092 0,-4.938913 -3.921808,-8.956934 -8.742746,-8.956934 -4.820938,0 -8.742746,4.018021 -8.742746,8.956934 0,4.704108 3.041005,9.723197 5.591784,13.105528 l 1.10759,1.466096 -0.448992,0.492517 c -4.292914,4.709835 -8.236485,7.000611 -12.056353,7.000611 -4.853009,0 -8.803452,-3.937843 -8.803452,-8.778253 0,-1.128207 0.303528,-2.210598 0.612783,-3.220831 0.623091,-1.986102 7.30872,-15.491371 16.63905,-33.609117 1.187767,-2.328573 3.468235,-3.721365 6.099191,-3.721365 2.630956,0 4.912568,1.395082 6.104917,3.731674 9.310858,18.077657 15.994197,31.574908 16.629887,33.585064 0.312691,1.021686 0.617364,2.106368 0.617364,3.23343 0.0011,4.840409 -3.949297,8.778253 -8.802306,8.778253 z M 54.126833,65.025571 c 3.793524,4.03062 7.20678,5.990378 10.42303,5.990378 3.969914,0 7.198763,-3.21854 7.198763,-7.173564 0,-0.906002 -0.268021,-1.858965 -0.54406,-2.756949 C 70.613546,59.220745 63.663332,45.200051 54.677764,27.754648 53.763745,25.963261 52.015883,24.89576 50,24.89576 c -2.015883,0 -3.762599,1.065211 -4.673183,2.84858 -9.002749,17.483201 -15.956399,31.509621 -16.534819,33.35255 -0.272603,0.889966 -0.540624,1.838348 -0.540624,2.745495 0,3.95617 3.228849,7.173564 7.198763,7.173564 3.21625,0 6.629506,-1.959758 10.42074,-5.989233 l -0.302383,-0.39974 C 42.86996,61.048784 39.652566,55.702113 39.652566,50.55474 c 0,-5.823152 4.642257,-10.560477 10.346289,-10.560477 5.704031,0 10.346289,4.737325 10.346289,10.560477 0,5.137065 -3.21854,10.486026 -5.917074,14.067654 z m -4.127978,-2.449985 -0.641418,-0.851023 c -3.133781,-4.16234 -4.933185,-8.233049 -4.933185,-11.167532 0,-3.192197 2.501527,-5.788791 5.574603,-5.788791 3.073075,0 5.574603,2.596594 5.574603,5.788791 0,2.928756 -1.798259,6.99832 -4.935477,11.167532 z M 50,46.371783 c -2.189982,0 -3.97106,1.877291 -3.97106,4.184102 0,2.399588 1.436317,5.759011 3.969915,9.326894 2.534743,-3.57361 3.972205,-6.934179 3.972205,-9.326894 0,-2.306811 -1.781078,-4.184102 -3.97106,-4.184102 z" id="path12" style="stroke-width:1.14539" /></svg>
                            </label>
                            <input class="form-control" type="text" id="airbnbSection6" placeholder="ex : airbnb.fr/rooms/553408749542273496">
                        </div>

                        <div class="mb-3">
                            <label for="gitesfrSection6" class="form-label">Gites.fr
                                <svg style="position: relative; top: -1px;" version="1.1" id="svg1" width="17" height="16.189604" viewBox="0 0 17 16.189604" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><defs id="defs1"/><g id="g1" transform="matrix(1.0624999,0,0,1.0624999,-354.36442,-354.90269)"><g id="g2" transform="matrix(0.02342979,0,0,0.02342979,333.51776,333.64007)"><path style="fill:#000000;fill-opacity:1" d="m 361.76166,634.29274 c 1.24391,-0.23919 3.04391,-0.23011 4,0.0202 0.95609,0.25029 -0.0617,0.446 -2.26166,0.4349 -2.2,-0.0111 -2.98225,-0.21589 -1.73834,-0.45508 z M 0.07894737,356.41667 c 0.04798392,-1.16495 0.2849403,-1.4019 0.60416667,-0.60417 0.28887061,0.72187 0.25334429,1.58438 -0.0789474,1.91667 -0.33229167,0.33229 -0.56864035,-0.25834 -0.52521927,-1.3125 z M 0.15789474,318 c 0,-1.375 0.22697368,-1.9375 0.50438596,-1.25 0.27741228,0.6875 0.27741228,1.8125 0,2.5 -0.27741228,0.6875 -0.50438596,0.125 -0.50438596,-1.25 z" id="path4"/><path style="fill:#000000;fill-opacity:1" d="m 301,665.91068 c -38.40616,-3.27242 -85.9133,-16.78908 -121.07379,-34.44774 -15.72185,-7.89599 -41.04305,-23.59116 -47.05467,-29.16652 -1.44565,-1.34074 -3.33554,-2.54149 -4.19975,-2.66833 -0.86421,-0.12684 -2.53775,-1.45927 -3.71898,-2.96095 -1.18123,-1.50169 -4.69134,-4.34683 -7.80025,-6.32253 C 107.26863,584.0634 81.20846,556.70963 65.854341,536.5 43.129073,506.5882 23.113856,466.66768 13.133839,431.3485 -5.1431462,366.66643 -4.102044,296.73464 16.066936,234.32968 45.340487,143.75422 106.34762,76.078574 198.5,31.95539 c 17.55213,-8.404078 22.8348,-10.424629 25.04124,-9.577938 3.0988,1.18912 1.13883,3.899465 -7.15578,9.895372 -30.66753,22.16857 -37.48933,27.735821 -52.90933,43.179105 C 117.14162,121.85642 85.509736,181.00389 72.12062,246.27476 67.854447,267.07202 66.653714,285.39222 67.255786,320.5 c 0.42585,24.83198 0.888256,32.06721 2.684237,42 3.337506,18.45831 7.504935,34.96366 12.927497,51.20011 16.223201,48.57618 39.41626,86.23323 83.02354,134.79989 4.10117,4.56759 18.54773,16.60528 24.00219,20 0.88371,0.55 3.94262,2.8 6.79757,5 10.16684,7.83449 13.93805,10.40663 24.04391,16.3991 29.15951,17.29069 59.95914,29.78143 92.76527,37.62081 27.08513,6.47228 32.76234,6.98815 76,6.90591 37.81692,-0.0719 39.45132,-0.1558 53.87764,-2.76492 26.04596,-4.71062 50.44871,-11.48128 72.8293,-20.20683 14.88284,-5.80239 19.51645,-6.08807 11.63308,-0.71721 -15.23348,10.37842 -45.51229,25.69688 -66.34002,33.56232 -31.12306,11.7534 -66.34049,19.37998 -100,21.65572 -17.19847,1.1628 -46.58618,1.14132 -60.5,-0.0442 z m 54.73683,-80.03729 c -45.94252,-7.43205 -80.39441,-20.60976 -116.91947,-44.72122 -30.34441,-20.03141 -60.35208,-49.83429 -80.48088,-79.93166 -23.75775,-35.52349 -37.54242,-71.75047 -44.59863,-117.20796 -3.10193,-19.98324 -3.05187,-59.57738 0.10094,-79.84064 3.89475,-25.03172 14.03221,-59.93895 22.4116,-77.17191 2.27318,-4.675 4.28161,-9.37041 4.46318,-10.43425 0.18156,-1.06383 1.44529,-3.39631 2.80827,-5.18328 1.36299,-1.78696 2.47816,-3.74401 2.47816,-4.34899 0,-1.78602 4.70783,-10.54611 6.44045,-11.98405 0.87805,-0.72872 2.33822,-2.95046 3.24481,-4.93719 2.42283,-5.30944 9.74357,-16.23769 12.7924,-19.0962 l 2.64933,-2.48397 12.47655,12.81591 c 6.8621,7.04875 13.68066,14.69154 15.15236,16.98397 1.47171,2.29243 5.90898,7.60382 9.86062,11.8031 18.24155,19.38474 40.82163,60.0114 51.73164,93.0769 8.54966,25.91187 11.19458,46.23419 10.38187,79.76931 -0.48667,20.08144 -0.87979,24.09946 -3.63213,37.12336 -4.56629,21.60736 -16.41708,56.98374 -20.7768,62.02182 -1.21386,1.40275 -1.20185,1.70534 0.0874,2.20006 0.94731,0.36352 4.3428,-2.01321 9.27045,-6.48902 9.23772,-8.39063 27.70526,-22.36341 35.06977,-26.53422 2.88678,-1.6349 4.96979,-3.25145 4.62891,-3.59233 -0.34088,-0.34088 5.17221,-4.40465 12.25131,-9.03061 C 417.28806,327.02178 533.61714,299.61184 641,320.13004 c 18.09182,3.4569 35.81934,7.94735 37.00241,9.37287 0.7664,0.92345 0.82064,1.95982 0.16658,3.18317 -0.53338,0.99765 -1.20392,5.41392 -1.49008,9.81392 -0.90301,13.88487 -7.86709,43.20902 -15.67408,66 -4.96884,14.50553 -18.32958,42.00701 -23.93645,49.27029 -2.26941,3.5561 -4.40867,6.87177 -6.81838,10.2477 -2.08526,3.49195 -3.80333,5.7078 -7.80404,10.84586 -1.94246,2.49468 -8.25251,10.10395 -8.95717,11.13615 -0.83135,1.21777 -30.35324,30.18584 -38.48879,36.63541 -37.01016,29.34028 -77.76785,48.07762 -124.5,57.23579 -17.31886,3.394 -29.2762,4.38784 -54.26317,4.51009 -21.75539,0.10644 -25.94137,-0.15277 -40.5,-2.5079 z M 325.67007,317.25 c -0.24638,-0.4125 -0.97619,-3.45 -1.6218,-6.75 -3.67743,-18.79702 -16.43543,-52.65465 -29.20303,-77.5 -14.93321,-29.05956 -39.32068,-61.51278 -66.50869,-88.5053 -11.19856,-11.11805 -29.8605,-27.50277 -38.90122,-34.15432 l -2.93533,-2.15962 8,-8.17992 c 10.91104,-11.156432 30.92846,-27.216991 45.5,-36.505987 4.125,-2.629585 8.625,-5.801407 10,-7.048494 3.34674,-3.035399 35.63838,-18.162706 49.99356,-23.419927 13.75052,-5.035781 33.42814,-10.19258 49.62693,-13.005438 24.9768,-4.33713 64.6827,-4.751875 88.87951,-0.928385 63.90441,10.097937 116.89702,36.939313 161.52002,81.811781 47.94634,48.21439 73.69102,102.4957 81.91283,172.70895 1.3344,11.39564 1.38592,27.07162 0.0916,27.87156 -0.53656,0.33161 -5.59906,-1.04273 -11.25,-3.05409 C 621.26748,280.80962 579.11529,273.84657 536.5,269.42446 c -6.10299,-0.6333 -10.31402,-1.00581 -13.14974,-1.27714 -1.00481,-0.0961 -6.39579,-0.12265 -11.81064,0.0482 -5.54965,0.17509 -18.56482,1.12021 -19.76414,1.22582 -8.54644,0.75251 -13.559,1.52834 -21.98441,2.73081 -11.29623,1.61218 -22.12969,3.937 -22.69358,3.93953 -0.64018,0.003 -6.52678,1.18587 -13.29381,2.81046 -30.87078,7.41129 -64.87673,19.4861 -97.17864,34.50613 -10.95295,5.09299 -10.33669,4.87688 -10.95497,3.84174 z" id="path2"/></g></g></svg>
                            </label>
                            <input class="form-control" type="text" id="gitesfrSection6" placeholder="ex : gites.fr/gi94796">
                        </div>

                        <div class="mb-3">
                            <label for="chambreshotesfrSection6" class="form-label">Chambres-hotes.fr
                                <svg style="position: relative; top: -1px;" version="1.1" id="svg1" width="17" height="16.189604" viewBox="0 0 17 16.189604" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><defs id="defs1"/><g id="g1" transform="matrix(1.0624999,0,0,1.0624999,-354.36442,-354.90269)"><g id="g2" transform="matrix(0.02342979,0,0,0.02342979,333.51776,333.64007)"><path style="fill:#000000;fill-opacity:1" d="m 361.76166,634.29274 c 1.24391,-0.23919 3.04391,-0.23011 4,0.0202 0.95609,0.25029 -0.0617,0.446 -2.26166,0.4349 -2.2,-0.0111 -2.98225,-0.21589 -1.73834,-0.45508 z M 0.07894737,356.41667 c 0.04798392,-1.16495 0.2849403,-1.4019 0.60416667,-0.60417 0.28887061,0.72187 0.25334429,1.58438 -0.0789474,1.91667 -0.33229167,0.33229 -0.56864035,-0.25834 -0.52521927,-1.3125 z M 0.15789474,318 c 0,-1.375 0.22697368,-1.9375 0.50438596,-1.25 0.27741228,0.6875 0.27741228,1.8125 0,2.5 -0.27741228,0.6875 -0.50438596,0.125 -0.50438596,-1.25 z" id="path4"/><path style="fill:#000000;fill-opacity:1" d="m 301,665.91068 c -38.40616,-3.27242 -85.9133,-16.78908 -121.07379,-34.44774 -15.72185,-7.89599 -41.04305,-23.59116 -47.05467,-29.16652 -1.44565,-1.34074 -3.33554,-2.54149 -4.19975,-2.66833 -0.86421,-0.12684 -2.53775,-1.45927 -3.71898,-2.96095 -1.18123,-1.50169 -4.69134,-4.34683 -7.80025,-6.32253 C 107.26863,584.0634 81.20846,556.70963 65.854341,536.5 43.129073,506.5882 23.113856,466.66768 13.133839,431.3485 -5.1431462,366.66643 -4.102044,296.73464 16.066936,234.32968 45.340487,143.75422 106.34762,76.078574 198.5,31.95539 c 17.55213,-8.404078 22.8348,-10.424629 25.04124,-9.577938 3.0988,1.18912 1.13883,3.899465 -7.15578,9.895372 -30.66753,22.16857 -37.48933,27.735821 -52.90933,43.179105 C 117.14162,121.85642 85.509736,181.00389 72.12062,246.27476 67.854447,267.07202 66.653714,285.39222 67.255786,320.5 c 0.42585,24.83198 0.888256,32.06721 2.684237,42 3.337506,18.45831 7.504935,34.96366 12.927497,51.20011 16.223201,48.57618 39.41626,86.23323 83.02354,134.79989 4.10117,4.56759 18.54773,16.60528 24.00219,20 0.88371,0.55 3.94262,2.8 6.79757,5 10.16684,7.83449 13.93805,10.40663 24.04391,16.3991 29.15951,17.29069 59.95914,29.78143 92.76527,37.62081 27.08513,6.47228 32.76234,6.98815 76,6.90591 37.81692,-0.0719 39.45132,-0.1558 53.87764,-2.76492 26.04596,-4.71062 50.44871,-11.48128 72.8293,-20.20683 14.88284,-5.80239 19.51645,-6.08807 11.63308,-0.71721 -15.23348,10.37842 -45.51229,25.69688 -66.34002,33.56232 -31.12306,11.7534 -66.34049,19.37998 -100,21.65572 -17.19847,1.1628 -46.58618,1.14132 -60.5,-0.0442 z m 54.73683,-80.03729 c -45.94252,-7.43205 -80.39441,-20.60976 -116.91947,-44.72122 -30.34441,-20.03141 -60.35208,-49.83429 -80.48088,-79.93166 -23.75775,-35.52349 -37.54242,-71.75047 -44.59863,-117.20796 -3.10193,-19.98324 -3.05187,-59.57738 0.10094,-79.84064 3.89475,-25.03172 14.03221,-59.93895 22.4116,-77.17191 2.27318,-4.675 4.28161,-9.37041 4.46318,-10.43425 0.18156,-1.06383 1.44529,-3.39631 2.80827,-5.18328 1.36299,-1.78696 2.47816,-3.74401 2.47816,-4.34899 0,-1.78602 4.70783,-10.54611 6.44045,-11.98405 0.87805,-0.72872 2.33822,-2.95046 3.24481,-4.93719 2.42283,-5.30944 9.74357,-16.23769 12.7924,-19.0962 l 2.64933,-2.48397 12.47655,12.81591 c 6.8621,7.04875 13.68066,14.69154 15.15236,16.98397 1.47171,2.29243 5.90898,7.60382 9.86062,11.8031 18.24155,19.38474 40.82163,60.0114 51.73164,93.0769 8.54966,25.91187 11.19458,46.23419 10.38187,79.76931 -0.48667,20.08144 -0.87979,24.09946 -3.63213,37.12336 -4.56629,21.60736 -16.41708,56.98374 -20.7768,62.02182 -1.21386,1.40275 -1.20185,1.70534 0.0874,2.20006 0.94731,0.36352 4.3428,-2.01321 9.27045,-6.48902 9.23772,-8.39063 27.70526,-22.36341 35.06977,-26.53422 2.88678,-1.6349 4.96979,-3.25145 4.62891,-3.59233 -0.34088,-0.34088 5.17221,-4.40465 12.25131,-9.03061 C 417.28806,327.02178 533.61714,299.61184 641,320.13004 c 18.09182,3.4569 35.81934,7.94735 37.00241,9.37287 0.7664,0.92345 0.82064,1.95982 0.16658,3.18317 -0.53338,0.99765 -1.20392,5.41392 -1.49008,9.81392 -0.90301,13.88487 -7.86709,43.20902 -15.67408,66 -4.96884,14.50553 -18.32958,42.00701 -23.93645,49.27029 -2.26941,3.5561 -4.40867,6.87177 -6.81838,10.2477 -2.08526,3.49195 -3.80333,5.7078 -7.80404,10.84586 -1.94246,2.49468 -8.25251,10.10395 -8.95717,11.13615 -0.83135,1.21777 -30.35324,30.18584 -38.48879,36.63541 -37.01016,29.34028 -77.76785,48.07762 -124.5,57.23579 -17.31886,3.394 -29.2762,4.38784 -54.26317,4.51009 -21.75539,0.10644 -25.94137,-0.15277 -40.5,-2.5079 z M 325.67007,317.25 c -0.24638,-0.4125 -0.97619,-3.45 -1.6218,-6.75 -3.67743,-18.79702 -16.43543,-52.65465 -29.20303,-77.5 -14.93321,-29.05956 -39.32068,-61.51278 -66.50869,-88.5053 -11.19856,-11.11805 -29.8605,-27.50277 -38.90122,-34.15432 l -2.93533,-2.15962 8,-8.17992 c 10.91104,-11.156432 30.92846,-27.216991 45.5,-36.505987 4.125,-2.629585 8.625,-5.801407 10,-7.048494 3.34674,-3.035399 35.63838,-18.162706 49.99356,-23.419927 13.75052,-5.035781 33.42814,-10.19258 49.62693,-13.005438 24.9768,-4.33713 64.6827,-4.751875 88.87951,-0.928385 63.90441,10.097937 116.89702,36.939313 161.52002,81.811781 47.94634,48.21439 73.69102,102.4957 81.91283,172.70895 1.3344,11.39564 1.38592,27.07162 0.0916,27.87156 -0.53656,0.33161 -5.59906,-1.04273 -11.25,-3.05409 C 621.26748,280.80962 579.11529,273.84657 536.5,269.42446 c -6.10299,-0.6333 -10.31402,-1.00581 -13.14974,-1.27714 -1.00481,-0.0961 -6.39579,-0.12265 -11.81064,0.0482 -5.54965,0.17509 -18.56482,1.12021 -19.76414,1.22582 -8.54644,0.75251 -13.559,1.52834 -21.98441,2.73081 -11.29623,1.61218 -22.12969,3.937 -22.69358,3.93953 -0.64018,0.003 -6.52678,1.18587 -13.29381,2.81046 -30.87078,7.41129 -64.87673,19.4861 -97.17864,34.50613 -10.95295,5.09299 -10.33669,4.87688 -10.95497,3.84174 z" id="path2"/></g></g></svg>
                            </label>
                            <input class="form-control" type="text" id="chambreshotesfrSection6" placeholder="ex : chambres-hotes.fr/ch96485">
                        </div>
                        
                    </div>

                    <!--Section 7 : Apparence-->
                    <div id="section7" class="card-body d-none">

                        <div class="mb-3">
                            <p>Choisissez le thème visuel que vous souhaitez activer sur votre site.</p>
                            <!--<span class="badge text-bg-primary fs-6">Thème actuel : <strong id="current_theme"></strong></span>-->
                            <p>Thème actif : <strong id="current_theme"></strong></p>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-4 g-5 p-4">
                            <div class="col">
                                <div id="thumbnail_theme_0" class="card rounded-bottom-0">
                                    <div class="card-body py-2">
                                        <span class="card-title fs-5 my-0">Fluffy <span class="badge text-bg-primary d-none">Actif</span></span>
                                    </div>
                                    <img id="picture1Section7" src="https://akobo.fr/wp-content/uploads/2025/03/template1.png" class="card-img-bottom border-top gallery_img" alt="..." style="aspect-ratio: 1.7 / 1; object-fit: cover; object-position: top;">
                                    <a onclick="select_theme(0, event)" href="#" id="picture1Section7_link" class="stretched-link" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                </div>
                                <button onclick="set_up_theme_modal_before_opening(0)" type="button" class="btn btn-dark w-100 rounded-top-0 rounded-bottom-3" data-bs-toggle="modal" data-bs-target="#themeModal">Voir plus <i class="bi bi-zoom-in"></i></button>
                            </div>
                            <div class="col">
                                <div id="thumbnail_theme_1" class="card rounded-bottom-0">
                                    <div class="card-body py-2">
                                        <span class="card-title fs-5 my-0">Baron <span class="badge text-bg-primary d-none">Actif</span></span>
                                    </div>
                                    <img id="picture2Section7" src="https://akobo.fr/wp-content/uploads/2025/03/template2.png" class="card-img-bottom border-top gallery_img" alt="..." style="aspect-ratio: 1.7 / 1; object-fit: cover; object-position: top;">
                                    <a onclick="select_theme(1, event)" href="#" id="picture2Section7_link" class="stretched-link" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                </div>
                                <button onclick="set_up_theme_modal_before_opening(1)" type="button" class="btn btn-dark w-100 rounded-top-0 rounded-bottom-3" data-bs-toggle="modal" data-bs-target="#themeModal">Voir plus <i class="bi bi-zoom-in"></i></button>
                            </div>
                            <div class="col">
                                <div id="thumbnail_theme_2" class="card rounded-bottom-0">
                                    <div class="card-body py-2">
                                        <span class="card-title fs-5 my-0">Personnalisé <span class="badge text-bg-primary d-none">Actif</span></span>
                                    </div>
                                    <img id="picture3Section7" src="https://akobo.fr/wp-content/uploads/2025/03/custom.png" class="card-img-bottom border-top gallery_img" alt="..." style="aspect-ratio: 1.7 / 1; object-fit: cover; object-position: center;">
                                    <a onclick="select_theme(2, event)" href="#" id="picture3Section7_link" class="stretched-link" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                </div>
                                <button onclick="set_up_theme_modal_before_opening(2)" type="button" class="btn btn-dark w-100 rounded-top-0 rounded-bottom-3" data-bs-toggle="modal" data-bs-target="#themeModal">En savoir plus <i class="bi bi-question-circle"></i></button>
                            </div>
                        </div>
                        
                    </div>

                    <!--Section 8 : Abonnement-->
                    <div id="section8" class="card-body d-none">

                        <div class="mb-3">
                            <script async src="https://js.stripe.com/v3/pricing-table.js"></script>
                                <stripe-pricing-table pricing-table-id="prctbl_1R18WxFGaevevpp6JiJhdA92"
                                publishable-key="pk_live_51R14PSFGaevevpp6wo2MDNaoNdOmcbm9X2PNYNFtB3hBSEnZr7xguNfbEqtl4iivfO8OKdJvFpkQVpClc8qL1npv005e1cOIFN">
                                </stripe-pricing-table>
                        </div>
                        
                    </div>
                </form>
            </div>

        </div>
          
        <!-- The Modal -->
        <div class="modal fade" tabindex="-1" id="myModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
            
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Sélection Image</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                
                    <!-- Modal body -->
                    <div class="modal-body text-center">
                        <div id="myCarousel" class="carousel slide" data-bs-interval="false">
                            <div id="carousel_inner" class="carousel-inner">
                                <div id="item_0" class="carousel-item active">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(0)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_0" src="" class="d-block w-100" alt="Image 1">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(0)" id="select_btn_0" type="button" class="btn btn-primary select_btn">Sélectionner image 1/<span class="nb_slides"></span></button>
                                        <div id="select_alert_0" class="alert alert-success d-none" role="alert">Image sélectionnée (1/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_1" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(1)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_1" src="" class="d-block w-100" alt="Image 2">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(1)" id="select_btn_1" type="button" class="btn btn-primary select_btn">Sélectionner image 2/<span class="nb_slides"></span></button>
                                        <div id="select_alert_1" class="alert alert-success d-none" role="alert">Image sélectionnée (2/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_2" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(2)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_2" src="" class="d-block w-100" alt="Image 3">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(2)" id="select_btn_2" type="button" class="btn btn-primary select_btn">Sélectionner image 3/<span class="nb_slides"></span></button>
                                        <div id="select_alert_2" class="alert alert-success d-none" role="alert">Image sélectionnée (3/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_3" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(3)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_3" src="" class="d-block w-100" alt="Image 4">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(3)" id="select_btn_3" type="button" class="btn btn-primary select_btn">Sélectionner image 4/<span class="nb_slides"></span></button>
                                        <div id="select_alert_3" class="alert alert-success d-none" role="alert">Image sélectionnée (4/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_4" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(4)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_4" src="" class="d-block w-100" alt="Image 5">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(4)" id="select_btn_4" type="button" class="btn btn-primary select_btn">Sélectionner image 5/<span class="nb_slides"></span></button>
                                        <div id="select_alert_4" class="alert alert-success d-none" role="alert">Image sélectionnée (5/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_5" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(5)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_5" src="" class="d-block w-100" alt="Image 6">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(5)" id="select_btn_5" type="button" class="btn btn-primary select_btn">Sélectionner image 6/<span class="nb_slides"></span></button>
                                        <div id="select_alert_5" class="alert alert-success d-none" role="alert">Image sélectionnée (6/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_6" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(6)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_6" src="" class="d-block w-100" alt="Image 7">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(6)" id="select_btn_6" type="button" class="btn btn-primary select_btn">Sélectionner image 7/<span class="nb_slides"></span></button>
                                        <div id="select_alert_6" class="alert alert-success d-none" role="alert">Image sélectionnée (7/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_7" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(7)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_7" src="" class="d-block w-100" alt="Image 8">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(7)" id="select_btn_7" type="button" class="btn btn-primary select_btn">Sélectionner image 8/<span class="nb_slides"></span></button>
                                        <div id="select_alert_7" class="alert alert-success d-none" role="alert">Image sélectionnée (8/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_8" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(8)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_8" src="" class="d-block w-100" alt="Image 9">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(8)" id="select_btn_8" type="button" class="btn btn-primary select_btn">Sélectionner image 9/<span class="nb_slides"></span></button>
                                        <div id="select_alert_8" class="alert alert-success d-none" role="alert">Image sélectionnée (9/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_9" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(9)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_9" src="" class="d-block w-100" alt="Image 10">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(9)" id="select_btn_9" type="button" class="btn btn-primary select_btn">Sélectionner image 10/<span class="nb_slides"></span></button>
                                        <div id="select_alert_9" class="alert alert-success d-none" role="alert">Image sélectionnée (10/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_10" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(10)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_10" src="" class="d-block w-100" alt="Image 11">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(10)" id="select_btn_10" type="button" class="btn btn-primary select_btn">Sélectionner image 11/<span class="nb_slides"></span></button>
                                        <div id="select_alert_10" class="alert alert-success d-none" role="alert">Image sélectionnée (11/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_11" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(11)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_11" src="" class="d-block w-100" alt="Image 12">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(11)" id="select_btn_11" type="button" class="btn btn-primary select_btn">Sélectionner image 12/<span class="nb_slides"></span></button>
                                        <div id="select_alert_11" class="alert alert-success d-none" role="alert">Image sélectionnée (12/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_12" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(12)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_12" src="" class="d-block w-100" alt="Image 13">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(12)" id="select_btn_12" type="button" class="btn btn-primary select_btn">Sélectionner image 13/<span class="nb_slides"></span></button>
                                        <div id="select_alert_12" class="alert alert-success d-none" role="alert">Image sélectionnée (13/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_13" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(13)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_13" src="" class="d-block w-100" alt="Image 14">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(13)" id="select_btn_13" type="button" class="btn btn-primary select_btn">Sélectionner image 14/<span class="nb_slides"></span></button>
                                        <div id="select_alert_13" class="alert alert-success d-none" role="alert">Image sélectionnée (14/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_14" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(14)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_14" src="" class="d-block w-100" alt="Image 15">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(14)" id="select_btn_14" type="button" class="btn btn-primary select_btn">Sélectionner image 15/<span class="nb_slides"></span></button>
                                        <div id="select_alert_14" class="alert alert-success d-none" role="alert">Image sélectionnée (15/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_15" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(15)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_15" src="" class="d-block w-100" alt="Image 16">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(15)" id="select_btn_15" type="button" class="btn btn-primary select_btn">Sélectionner image 16/<span class="nb_slides"></span></button>
                                        <div id="select_alert_15" class="alert alert-success d-none" role="alert">Image sélectionnée (16/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_16" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(16)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_16" src="" class="d-block w-100" alt="Image 17">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(16)" id="select_btn_16" type="button" class="btn btn-primary select_btn">Sélectionner image 17/<span class="nb_slides"></span></button>
                                        <div id="select_alert_16" class="alert alert-success d-none" role="alert">Image sélectionnée (17/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_17" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(17)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_17" src="" class="d-block w-100" alt="Image 18">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(17)" id="select_btn_17" type="button" class="btn btn-primary select_btn">Sélectionner image 18/<span class="nb_slides"></span></button>
                                        <div id="select_alert_17" class="alert alert-success d-none" role="alert">Image sélectionnée (18/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_18" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(18)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_18" src="" class="d-block w-100" alt="Image 19">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(18)" id="select_btn_18" type="button" class="btn btn-primary select_btn">Sélectionner image 19/<span class="nb_slides"></span></button>
                                        <div id="select_alert_18" class="alert alert-success d-none" role="alert">Image sélectionnée (19/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_19" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(19)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_19" src="" class="d-block w-100" alt="Image 20">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(19)" id="select_btn_19" type="button" class="btn btn-primary select_btn">Sélectionner image 20/<span class="nb_slides"></span></button>
                                        <div id="select_alert_19" class="alert alert-success d-none" role="alert">Image sélectionnée (20/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                            </div>
                            <button id="carousel-control-prev" class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button id="carousel-control-next" class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                            <div id="loading_image_icon" class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p id="no_slide" class="d-none">Aucune image à afficher.</p>
                        <br>
                        <input type="range" class="form-range slider" min="0" max="0" id="customRange1" onchange="move_carousel_to(this.value)">
                    </div>
                
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <span id="upload_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title=".">
                            <span id="upload_span" class="btn btn-primary btn-file">
                                Charger image <i class="bi bi-upload"></i><input id="upload_image" type="file" accept="image/png, image/jpeg, image/webp">
                            </span>
                        </span>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="modal fade" tabindex="-1" id="published_modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header border-bottom-0">
                        <h1 class="modal-title mt-4 fs-3" style="position: absolute; left: 0; right: 0; margin-inline: auto; width: fit-content;">Publié !</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body my-2 text-center">
                        <p>Votre site est publié ici :</p>
                        <div class="alert alert-primary" role="alert">
                            <a href="#" target="_blank" id="published_modal_link" class="alert-link"></a>
                        </div>
                        <small>Vous souhaitez changer l'adresse de votre site ? <u style="color: #0d6efd;">S'abonner</u></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <div class="modal fade" id="themeModal" tabindex="-1" aria-labelledby="themeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="themeModalLabel">Thème <span id="theme_name"></span>
                        <br>
                        <small class="fs-6">Faites défiler pour voir l'ensemble de la page d'exemple.</small>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; right: 18px; top: 24px;"></button>
                </div>
                <div class="modal-body">
                    <img id="theme_img" class="w-100" src="" alt="..." />
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button onclick="select_theme(previewed_theme)" data-bs-dismiss="modal" type="button" class="btn btn-primary">Choisir ce thème</button>
                </div>
            </div>
            </div>
        </div>  

        <!--<div class="container mt-auto">
            <footer class="py-3 border-top">
                <p class="text-center text-body-secondary">Akobo © 2025</p>
            </footer>
        </div>-->
    </body>
</html>
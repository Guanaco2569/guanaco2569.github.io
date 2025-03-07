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

            document.addEventListener("DOMContentLoaded", (event) => {
                nb_slides_spans = document.getElementsByClassName("nb_slides");
                delete_buttons = document.getElementsByClassName("delete_btn");
                select_buttons = document.getElementsByClassName("select_btn");
                upload_span = document.getElementById("upload_span");
                nb_fields_span = document.getElementById("nb_fields");
                copy_link_btn = document.getElementById("copy_link_btn");
                save_btn = document.getElementById("save_btn");
                published_modal = new bootstrap.Modal(document.getElementById('published_modal'));

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
            });

            function init_form_values() {
                //PICTURES OF ALL SECTIONS

                document.getElementById("welcomePictureSection1").src = images_list[selected_images[0]];
                document.getElementById("welcomePictureSection1_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_modal_before_opening(0);
                });
                document.getElementById("picture1Section2").src = images_list[selected_images[1]];
                document.getElementById("picture1Section2_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_modal_before_opening(1);
                });
                document.getElementById("picture2Section2").src = images_list[selected_images[2]];
                document.getElementById("picture2Section2_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_modal_before_opening(2);
                });
                for (let i = 0; i < params.galerie_images.length; i++) {
                    document.getElementById("picture"+(i+1).toString()+"Section4").src = images_list[selected_images[i+3]];
                    document.getElementById("picture"+(i+1).toString()+"Section4_link").addEventListener("click", (ev) => {
                        ev.preventDefault();
                        set_up_modal_before_opening(i+3);
                    });
                }
                document.getElementById("contactPictureSection5").src = images_list[selected_images[9]];
                document.getElementById("contactPictureSection5_link").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    set_up_modal_before_opening(9);
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
                params.image_accueil = document.getElementById("welcomePictureSection1").src;

                params.esprit[0].Qual = document.getElementById("qualifier1Section2").value;
                params.esprit[0].Desc = document.getElementById("textQualifier1Section2").value;
                params.esprit[0].Img = document.getElementById("picture1Section2").src;
                params.esprit[1].Qual = document.getElementById("qualifier2Section2").value;
                params.esprit[1].Desc = document.getElementById("textQualifier2Section2").value;
                params.esprit[1].Img = document.getElementById("picture2Section2").src;

                params.caracteristiques = [];
                document.querySelectorAll(".item_input").forEach(elt => {
                    params.caracteristiques.push(elt.value);
                });

                params.galerie_images = [];
                document.querySelectorAll(".gallery_img").forEach(elt => {
                    params.galerie_images.push(elt.src);
                });

                params.email = document.getElementById("emailSection5").value;
                params.telephone = document.getElementById("phoneSection5").value;
                params.contact_image = document.getElementById("contactPictureSection5").src;

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

            function set_up_modal_before_opening(img_field_id) {
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
                            <a id="link_sect_3" onclick="go_to_section(3, event)" class="nav-link" href="#">Caractéristiques</a>
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
                            <a id="link_sect_7" onclick="go_to_section(7, event)" class="nav-link" href="#">Abonnement</a>
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
                            <div class="card" style="width: 18rem; height: 12rem;">
                                <img id="welcomePictureSection1" src="" class="card-img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); width: 18rem; height: 12rem; object-fit: contain;">
                                <div class="card-img-overlay d-flex">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="welcomePictureSection1_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>
                        
                    </div>

                    <!--Section 2 : Description-->
                    <div id="section2" class="card-body d-none">

                        <div class="mb-3">
                            <h5>Premier qualificatif</h5>
                        </div>

                        <div class="mb-3">
                            <label for="qualifier1Section2" class="form-label">Votre logement est :</label>
                            <input class="form-control" type="text" id="qualifier1Section2" placeholder="ex : Moderne" data-section="2" required>              
                        </div>

                        <div class="mb-3">
                            <label for="textQualifier1Section2" class="form-label">Développez avec un court texte</label>
                            <textarea class="form-control" id="textQualifier1Section2" name="text" maxlength="200" 
                            placeholder="ex : La Villa Florentine a été entièrement rénovée l'année dernière. Elle possède les équipements les plus récents dans chacune de ses pièces." 
                            rows="4" aria-describedby="countHelp1Section2" data-section="2" required></textarea>
                            <div id="countHelp1Section2" class="form-text"></div>
                        </div>

                        <div class="mb-3">
                            <label for="picture1Section2" class="form-label">Photo d'illustration</label>
                            <div class="card" style="width: 18rem; height: 12rem;">
                                <img id="picture1Section2" src="" class="card-img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); width: 18rem; height: 12rem; object-fit: contain;">
                                <div class="card-img-overlay d-flex">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="picture1Section2_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <hr class="bg-dark border-top border-dark" />
                        </div>

                        <div class="mb-3">
                            <h5>Second qualificatif</h5>
                        </div>

                        <div class="mb-3">
                            <label for="qualifier2Section2" class="form-label">Votre logement est :</label>
                            <input class="form-control" type="text" id="qualifier2Section2" placeholder="ex : Convivial" data-section="2" required>
                        </div>

                        <div class="mb-3">
                            <label for="textQualifier2Section2" class="form-label">Développez avec un court texte</label>
                            <textarea class="form-control" id="textQualifier2Section2" name="text" maxlength="200" 
                            placeholder="ex : La Villa Florentine offre des espaces ouverts et lumineux, créant ainsi une atmosphère chaleureuse et propice aux moments de partage." 
                            rows="4" aria-describedby="countHelp2Section2" data-section="2" required></textarea>
                            <div id="countHelp2Section2" class="form-text"></div>
                        </div>

                        <div class="mb-3">
                            <label for="picture2Section2" class="form-label">Photo d'illustration</label>
                            <div class="card" style="width: 18rem; height: 12rem;">
                                <img id="picture2Section2" src="" class="card-img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); width: 18rem; height: 12rem; object-fit: contain;">
                                <div class="card-img-overlay d-flex">
                                    <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                </div>
                                <a href="#" id="picture2Section2_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                            </div>
                        </div>

                    </div>

                    <!--Section 3 : Caractéristiques-->
                    <div id="section3" class="card-body d-none">

                        <div class="mb-3">
                            <p>Indiquez les divers éléments de votre logement (chambres, couchages, WiFi, parking, etc). Maximum 12 éléments.</p>
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
                                        <div class="card">
                                            <img id="picture1Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture1Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card">
                                            <img id="picture2Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture2Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card">
                                            <img id="picture3Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture3Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card">
                                            <img id="picture4Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture4Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card">
                                            <img id="picture5Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
                                                <h5 class="card-title text-white align-self-center mx-auto">Sélectionner image</h5>
                                            </div>
                                            <a href="#" id="picture5Section4_link" class="stretched-link" data-bs-toggle="modal" data-bs-target="#myModal" style="opacity: 0; overflow: hidden; height: 0; width: 0; display: block;">Modal</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card">
                                            <img id="picture6Section4" src="" class="card-img gallery_img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); aspect-ratio: 1.5 / 1; object-fit: contain;">
                                            <div class="card-img-overlay d-flex">
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
                            <div class="card" style="width: 18rem; height: 12rem;">
                                <img id="contactPictureSection5" src="" class="card-img" alt="..." style="-webkit-filter: brightness(70%); filter: brightness(70%); width: 18rem; height: 12rem; object-fit: contain;">
                                <div class="card-img-overlay d-flex">
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
                            <label for="instaSection6" class="form-label">Instagram</label>
                            <input class="form-control" type="text" id="instaSection6" placeholder="ex : instagram.com/villa.florentine">
                        </div>

                        <div class="mb-3">
                            <label for="fbSection6" class="form-label">Facebook</label>
                            <input class="form-control" type="text" id="fbSection6" placeholder="ex : facebook.com/villa.florentine">
                        </div>

                        <div class="mt-4">
                            <hr class="bg-dark border-top border-dark" />
                        </div>

                        <div class="mb-3">
                            <h5>Plateformes de location</h5>
                        </div>

                        <div class="mb-3">
                            <label for="bookingSection6" class="form-label">Booking.com</label>
                            <input class="form-control" type="text" id="bookingSection6" placeholder="ex : booking.com/hotel/fr/villa.florentine">
                        </div>

                        <div class="mb-3">
                            <label for="airbnbSection6" class="form-label">Airbnb</label>
                            <input class="form-control" type="text" id="airbnbSection6" placeholder="ex : airbnb.fr/rooms/553408749542273496">
                        </div>

                        <div class="mb-3">
                            <label for="gitesfrSection6" class="form-label">Gites.fr</label>
                            <input class="form-control" type="text" id="gitesfrSection6" placeholder="ex : gites.fr/gi94796">
                        </div>

                        <div class="mb-3">
                            <label for="chambreshotesfrSection6" class="form-label">Chambres-hotes.fr</label>
                            <input class="form-control" type="text" id="chambreshotesfrSection6" placeholder="ex : chambres-hotes.fr/ch96485">
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
                        <span id="upload_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title=".">
                            <span id="upload_span" class="btn btn-secondary btn-file">
                                Charger image <i class="bi bi-upload"></i><input id="upload_image" type="file" accept="image/png, image/jpeg, image/webp">
                            </span>
                        </span>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
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

        <!--<div class="container mt-auto">
            <footer class="py-3 border-top">
                <p class="text-center text-body-secondary">Akobo © 2025</p>
            </footer>
        </div>-->
    </body>
</html>

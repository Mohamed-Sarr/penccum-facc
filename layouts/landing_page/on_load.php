<?php

if (Registry::load('settings')->groups_section_status !== 'enable' && Registry::load('settings')->faq_section_status !== 'enable') {
    Registry::load('appearance')->body_class = Registry::load('appearance')->body_class.' footer_only_layout';
} else if (Registry::load('settings')->groups_section_status === 'enable' && Registry::load('settings')->faq_section_status !== 'enable') {
    Registry::load('appearance')->body_class = Registry::load('appearance')->body_class.' footer_divider_style_2';
}

if (isset(Registry::load('settings')->hero_section_animation) && Registry::load('settings')->hero_section_animation === 'enable') {
    Registry::load('appearance')->body_class .= ' animated_hero_image';
}

?>
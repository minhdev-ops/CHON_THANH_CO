const fs = require('fs');
const path = require('path');

const files = [
  'ProjectsPage.vue',
  'CertificatesPage.vue',
  'CapabilityProfilePage.vue',
  'AboutPage.vue',
  'ContactPage.vue',
  'ProductsPage.vue',
  'CertificationDossierPage.vue',
  'NewsPage.vue'
];

for (const file of files) {
  const filePath = path.join('resources/js/pages', file);
  let content = fs.readFileSync(filePath, 'utf8');
  
  // Replace the hero section block
  // It starts with <section class="relative w-full h-[320px]...
  // and ends with </section>
  const regex = /<section class="relative w-full h-\[.*?px\].*?flex items-center justify-center overflow-hidden bg-primary(?:-container)?">[\s\S]*?<\/section>/;
  
  if (regex.test(content)) {
    content = content.replace(regex, '<GlobalBanner />');
    
    // Add import statement if not already there
    if (!content.includes('import GlobalBanner')) {
      const scriptStart = content.indexOf('<script setup lang="ts">') + '<script setup lang="ts">'.length;
      content = content.slice(0, scriptStart) + '\nimport GlobalBanner from \'../components/GlobalBanner.vue\'' + content.slice(scriptStart);
    }
    
    fs.writeFileSync(filePath, content, 'utf8');
    console.log('Updated', file);
  } else {
    console.log('Skipped (no match)', file);
  }
}

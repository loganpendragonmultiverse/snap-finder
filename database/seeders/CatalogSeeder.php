<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\SnapCategory;
class CatalogSeeder extends Seeder {
 public function run(): void {
  foreach(['friends','gaming','creators','local','business','lifestyle','music','art','dating','adult'] as $i=>$slug)
   SnapCategory::firstOrCreate(['slug'=>$slug],['name'=>ucfirst($slug),'is_nsfw'=>$slug==='adult','sort_order'=>$i]);
 }
}

<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TestSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker::create();
        $this->truncateTables();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = $this->createCategories();
        $tags = $this->createTags();
        $fields = $this->createFields();
        $fieldValues = $this->setValuesToFields($fields);
        $authors = $this->createAuthors();

        $posts = $this->createPosts($categories, $tags, $authors, $fieldValues);

        $users = $this->createUsers();
    }

    /**
     * Truncate the database tables.
     */
    private function truncateTables()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('categories')->truncate();
        \DB::table('tags')->truncate();
        \DB::table('posts')->truncate();
        \DB::table('fields')->truncate();
        \DB::table('users')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function createCategories()
    {
        return factory(\Flashtag\Core\Category::class, 5)->create();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function createTags()
    {
        return factory(\Flashtag\Core\Tag::class, 5)->create();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function createFields()
    {
        return new Collection([
            \Flashtag\Core\Field::create([
                'name'        => 'pull_quote',
                'label'       => 'Pull quote',
                'description' => 'Pull quotes',
                'template'    => 'string',
            ]),
            \Flashtag\Core\Field::create([
                'name'        => 'copyright',
                'label'       => 'Copyright',
                'description' => 'Copyright',
                'template'    => 'string',
            ]),
            \Flashtag\Core\Field::create([
                'name'        => 'footnotes',
                'label'       => 'Footnotes',
                'description' => 'Footnotes',
                'template'    => 'rich_text',
            ]),
            \Flashtag\Core\Field::create([
                'name'        => 'disclaimer',
                'label'       => 'Disclaimer',
                'description' => 'Disclaimer',
                'template'    => 'rich_text',
            ]),
            \Flashtag\Core\Field::create([
                'name'        => 'teaser',
                'label'       => 'Teaser',
                'description' => 'Teaser',
                'template'    => 'rich_text',
            ]),
        ]);
    }

    /**
     * Make an array of field values to sync to posts.
     *
     * @param \Illuminate\Support\Collection $fields
     * @return array
     */
    private function setValuesToFields(Collection $fields)
    {
        return $fields->reduce(function($carry, $field) {
            $carry[$field->name] = $this->faker->word;
            return $carry;
        }, []);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function createAuthors()
    {
        return factory(\Flashtag\Core\Author::class, 5)->create();
    }

    /**
     * @param \Illuminate\Support\Collection $categories
     * @param \Illuminate\Support\Collection $tags
     * @param Collection $authors
     * @param array $fieldValues
     * @return Collection
     */
    private function createPosts(Collection $categories, Collection $tags, Collection $authors, array $fieldValues)
    {
        $posts = factory(\Flashtag\Core\Post::class, 10)->create();

        return $posts->map(function ($post) use ($categories, $tags, $authors, $fieldValues) {
            $post->changeCategoryTo($categories->random());
            $post->addTags($this->faker->randomElements($tags->all(), 2));
            $post->saveFields($fieldValues);
            $post->author_id = $this->faker->randomElement($authors->lists('id')->toArray());
            $post->save();

            return $post;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function createUsers()
    {
        return new Collection([
            \Flashtag\Cms\User::create([
                'email' => 'test@test.com',
                'name' => $this->faker->name,
                'password' => \Hash::make('password'),
            ]),
        ]);
    }
}

<?php

namespace Flashtag\Admin\Http\Controllers;

use Flashtag\Admin\Http\Requests\PageCreateRequest;
use Flashtag\Admin\Http\Requests\PageDestroyRequest;
use Flashtag\Admin\Http\Requests\PageUpdateRequest;
use Flashtag\Data\Page;
use Illuminate\Support\Facades\Storage;

class PagesController extends Controller
{
    public function index()
    {
        return view('admin::pages.index');
    }

    public function show($id)
    {
        return redirect()->route('admin.pages.edit', [$id], 301);
    }

    public function create()
    {
        $page = new Page();

        return view('admin::pages.create', compact('page'));
    }

    public function store(PageCreateRequest $request)
    {
        $page = Page::create($this->buildPageFromRequest($request));

        $this->handleImageUpload($page, $request->file('image'));

        return redirect()->route('admin.pages.index');
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);
        $page->lock(auth()->user()->id);

        return view('admin::pages.edit', compact('page', 'includes'));
    }

    public function update(PageUpdateRequest $request, $id)
    {
        $page = Page::findOrFail($id);

        $page->update($this->buildPageFromRequest($request));

        $this->handleImageUpload($page, $request->file('image'));

        return redirect()->route('admin.pages.index');
    }

    private function buildPageFromRequest($request)
    {
        $data['title'] = $request->get('title');
        $data['subtitle'] = $request->get('subtitle');
        $data['slug'] = $request->get('slug');
        $data['body'] = $request->get('body');
        $data['is_published'] = $request->get('is_published', false);
        $data['meta_description'] = $request->get('meta_description');
        $data['meta_canonical'] = $request->get('meta_canonical');

        if ($request->get('start_showing_at')) {
            $data['start_showing_at'] = $request->get('start_showing_at');
        }
        if ($request->get('stop_showing_at')) {
            $data['stop_showing_at'] = $request->get('stop_showing_at');
        }

        return $data;
    }

    private function handleImageUpload(Page $page, $image)
    {
        if (! empty($image)) {
            $page->addImage($image);
        }
    }

    public function destroy(PageDestroyRequest $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages.index');
    }
}
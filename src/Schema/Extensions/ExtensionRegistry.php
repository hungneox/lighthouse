<?php

namespace Nuwave\Lighthouse\Schema\Extensions;

class ExtensionRegistry
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $extensions;

    /**
     * Create instance of extension registry.
     */
    public function __construct()
    {
        $this->extensions = collect();
    }

    /**
     * Register graphql extension.
     *
     * @param GraphQLExtension $extension
     */
    public function register(GraphQLExtension $extension)
    {
        $this->extensions->put($extension->name(), $extension);
    }

    /**
     * Register graphql extensions.
     *
     * @param array $extensions
     */
    public function registerMany($extensions)
    {
        foreach ($extensions as $extension) {
            $this->register($extension);
        }
    }

    /**
     * Get extension.
     *
     * @param name $name
     *
     * @return GraphQLExtension|null
     */
    public function get($name)
    {
        return $this->extensions->get($name);
    }

    /**
     * Get active extensions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function active()
    {
        $extensions = config('lighthouse.extensions', []);

        if (is_string($extensions)) {
            $extensions = explode(',', $extensions);
        }

        return $this->extensions->only($extensions);
    }

    /**
     * Handle request start.
     *
     * @param ExtensionRequest $request
     */
    public function requestDidStart(ExtensionRequest $request)
    {
        $this->active()->each(function (GraphQLExtension $extension) use ($request) {
            $extension->requestDidStart($request);
        });
    }

    /**
     * Get output for all extensions.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->active()
            ->mapWithKeys(function (GraphQLExtension $extension) {
                return [$extension->name() => $extension->toArray()];
            })
            ->toArray();
    }
}

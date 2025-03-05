<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class PluginController extends Controller
{
    public function index()
    {
        return response()->json(Plugin::all());
    }

    public function show($id)
    {
        return response()->json(Plugin::findOrFail($id));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:50000'
        ]);

        $file = $request->file('plugin_file');

        // Временный путь в storage для загрузки архива
        $zipPath = $file->storeAs('plugins/tmp', $file->getClientOriginalName());

        $zip = new ZipArchive;
        $zipFullPath = storage_path("app/{$zipPath}");

        if ($zip->open($zipFullPath) === true) {
            $pluginSlug = uniqid('plugin_');

            // Папка в storage/app/plugins/unpacked/ для разархивирования
            $backendPath = storage_path("app/plugins/unpacked/{$pluginSlug}");

            // Создаем директорию если ее нет
            if (!File::exists($backendPath)) {
                File::makeDirectory($backendPath, 0755, true, true);
            }

            // Распаковка в backend
            $zip->extractTo($backendPath);
            $zip->close();

            // Проверка наличия manifest-файла
            $manifestPath = "{$backendPath}/plugin.json";
            if (!file_exists($manifestPath)) {
                return response()->json(['message' => 'plugin.json не найден'], 422);
            }

            $manifestData = json_decode(file_get_contents($manifestPath), true);
            if (!$manifestData || !isset($manifestData['slug'])) {
                return response()->json(['message' => 'Некорректный plugin.json'], 422);
            }

            if (Plugin::where('slug', $manifestData['slug'])->exists()) {
                return response()->json(['message' => 'Плагин с таким slug уже существует'], 422);
            }

            // Запись в БД
            $plugin = Plugin::create([
                'slug' => $manifestData['slug'],
                'name' => $manifestData['name'],
                'description' => $manifestData['description'] ?? '',
                'version' => $manifestData['version'] ?? '',
                'author' => $manifestData['author'] ?? '',
                'type' => $manifestData['type'] ?? 'frontend',
                'enabled' => false
            ]);

            // Удаляем временный zip-файл
            File::delete($zipFullPath);

            return response()->json([
                'message' => 'Плагин успешно загружен и сохранён на бэке',
                'plugin' => $plugin
            ]);
        } else {
            return response()->json(['message' => 'Ошибка при распаковке'], 422);
        }
    }

    public function enable($id)
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->update(['enabled' => true]);

        return response()->json(['message' => 'Плагин включен']);
    }

    public function disable($id)
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->update(['enabled' => false]);

        return response()->json(['message' => 'Плагин отключен']);
    }

    public function delete($id)
    {
        $plugin = Plugin::findOrFail($id);
        $pluginDir = storage_path("app/plugins/unpacked/{$plugin->slug}");

        if (File::exists($pluginDir)) {
            File::deleteDirectory($pluginDir);
        }

        $plugin->delete();

        return response()->json(['message' => 'Плагин удален']);
    }

    /**
     * Отдаёт список включенных плагинов для фронта.
     */
    public function listFrontendPlugins()
    {
        $plugins = Plugin::where('enabled', true)->get();

        return response()->json([
            'plugins' => $plugins->map(function ($plugin) {
                return [
                    'slug' => $plugin->slug,
                    'url' => url("/api/plugins/serve/{$plugin->slug}/plugin.js")
                ];
            })
        ]);
    }

    /**
     * API для загрузки конкретного plugin.js с бэка.
     */
    public function servePluginJS($slug)
    {
        $pluginPath = storage_path("app/plugins/unpacked/{$slug}/plugin.js");

        if (!file_exists($pluginPath)) {
            return response()->json(['message' => 'Плагин не найден'], 404);
        }

        return response()->file($pluginPath, [
            'Content-Type' => 'application/javascript'
        ]);
    }
}

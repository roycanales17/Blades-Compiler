<?php

	namespace App\Content;

	use Closure;
	use Error;
	use Exception;

	class Blade
	{
		private static string $path = '';

		use Properties;

		public static function directive(string $directive, Closure $callback, bool $replaceContent = false): void
		{
			self::register($directive, $callback, $replaceContent);
		}

		public static function wrap(string $prefix, string $suffix, Closure $callback): void
		{
			self::tag($prefix, $suffix, $callback);
		}

		public static function compile(string $content, array $directives = []): string
		{
			$compiler = new Compile($content, $directives);
			$compiler->importMainDirectory(dirname(getcwd()));
			$compiler->importDirectives();
			$compiler->startCompile();

			return $compiler->getTemplate();
		}

		public static function render(string $path, array $directives = [], array $extract = [], object|null $onError = null): void
		{
			# In case if it will throw an error
			self::$path = $path;

			if (file_exists($path = self::getProjectRootPath().'/'.$path)) {

				# Fetch the content
				$content = file_get_contents($path);

				# Compile
				self::eval(self::compile($content, $directives), $extract, $onError);
			}
		}

		public static function eval(string $script, array $data = [], object|null $onError = null): void
		{
			$tempFile = tempnam(sys_get_temp_dir(), 'tpl_') . '.php';
			file_put_contents($tempFile, $script);

			try {
				(static function () use ($tempFile, $data) {
					extract($data, EXTR_SKIP);
					include $tempFile;
				})();
			} catch (Exception|Error $e) {
				if (is_callable($onError)) {
					$onError([
						'message' => $e->getMessage(),
						'line' => $e->getLine(),
						'path' => self::$path ?: $tempFile,
						'code' => (int) $e->getCode(),
						'content' => $script
					]);
				}
			} finally {
				unlink($tempFile);
			}
		}

		public static function getProjectRootPath(): string
		{
			$vendorPos = strpos(__DIR__, 'vendor');
			if ($vendorPos !== false) {
				return substr(__DIR__, 0, $vendorPos);
			}

			return dirname(__DIR__);
		}

		public static function resolveError(array $traces, array $attr): void
		{
			$stop = false;
			$expression = $attr['expression'] ?? '';
			$candidatePaths = $attr['candidatePaths'] ?? [];
			$resolvedPath = $attr['resolvedPath'] ?? '';
			$template = $attr['template'] ?? '';

			if (!$resolvedPath) {
				foreach ($traces as $trace) {
					$file = $trace['file'] ?? '';
					$file = explode(DIRECTORY_SEPARATOR, $file);
					$file = array_pop($file);

					if ($stop) {
						$resolvedPath = $trace['args'][0] ?? '';
						break;
					}

					if (in_array($file, ['Blade.php', 'Component.php']) && ($trace['function'] ?? '') == 'compile')
						$stop = true;
				}
			}

			$title = ucfirst($template);
			throw new Exception("
				<div style='font-family: sans-serif; background: #fdfdfd; border: 1px solid #ccc; padding: 20px; border-radius: 8px; color: #333;'>
					<h2 style='margin-top: 0; color: #d33;'>Blade $title Path Not Found</h2>
					<p>
						<strong>Template:</strong> <b style='color: #d33;'>@$template($expression)</b><br/>
						<strong>Resolved Path:</strong> <b style='color: blue;'>{$resolvedPath}</b>
					</p>
					<p><strong>Tried the following paths:</strong></p>
					<ul style='margin-top: 5px; padding-left: 20px; color: #555;'>
						" . implode('', array_map(fn($p) => "<li>$p</li>", $candidatePaths)) . "
					</ul>
				</div>
			");
		}
	}
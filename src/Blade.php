<?php

	namespace App\Content;

	use Closure;
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

		public static function compile(string $template, array $directives = []): string
		{
			$compiler = new Compile($template, $directives);
			$compiler->importMainDirectory(dirname(getcwd()));
			$compiler->importDirectives();
			$compiler->startCompile();

			return $compiler->getTemplate();
		}

		public static function render(string $path, array $directives = [], array $extract = []): void
		{
			# In case if it will throw an error
			self::$path = $path;

			if (file_exists($path = self::getProjectRootPath().'/'.$path)) {

				# Fetch the content
				$content = file_get_contents($path);

				# Compile
				self::eval(self::compile($content, $directives), $extract);
			}
		}

		public static function eval(string $script, array $data = []): void
		{
			$tempFile = tempnam(sys_get_temp_dir(), 'tpl_') . '.php';
			file_put_contents($tempFile, $script);

			try {
				(static function () use ($tempFile, $data) {
					extract($data, EXTR_SKIP);
					include $tempFile;
				})();
			} catch (Exception $e) {
				throw new Exception(
					str_replace($tempFile, self::$path, $e->getMessage()),
					(int) $e->getCode(),
					$e
				);
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
	}
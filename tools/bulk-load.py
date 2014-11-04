
from urllib import request, error
from lxml import etree
from os import makedirs, path, getcwd
from urllib.parse import urljoin, urldefrag
import re
from hashlib import sha1
from time import sleep
from random import random


class TileLoader(object):
	def __init__(self, z, x, y, shared = set()):
		self.coords = z, x, y
		self.resources = []
		self.shared = shared
		self.completed = set()

	def load_resource(self, url):
		try:
			# print('Load url', url, '...', end=' ', flush=True)
			res = request.urlopen(url)
			# print('Done.')
			return res
		except error.HTTPError as e:
			# print('Failed!')
			return None
	
	def process_resource_attr(self, url, res, attr, search, replace):
		attr_externals = []
		
		refs = res.findall('//*[@' + attr + ']')
		# print(refs)
		for r in refs:
			srcurl = search(r.get(attr))
			if srcurl is None:
				continue
			src = urljoin(url, srcurl)
			srcurl, srcfrag = urldefrag(src)
			# print(srcurl)
			dst = self.resolve(srcurl)
			if len(srcfrag) > 0:
				dst = dst + '#' + srcfrag
			r.set(attr, replace(dst))
			attr_externals.append(srcurl)
		
		return attr_externals
	
	def process_resource(self, url, res):
		externals = []
		
		search_val = lambda value: value
		replace_val = lambda dst: dst
		
		# all src
		attr_externals = self.process_resource_attr(
			url, res,
			'src',
			search_val, replace_val
		)
		externals.extend(attr_externals)

		# all shader
		attr_externals = self.process_resource_attr(
			url, res,
			'shader',
			search_val, replace_val
		)
		externals.extend(attr_externals)

		# all dataflow
		search_dataflow = lambda src: src[10:-2] if src.startswith("dataflow['") else None
		attr_externals = self.process_resource_attr(
			url, res,
			'compute',
			search_dataflow,
			lambda dst: "dataflow['%s']" % dst
		)
		externals.extend(attr_externals)
		
		return externals

	def load(self, endpoint):
		self.resources = []
		url_model = endpoint + '/%d/%d/%d.xml' % self.coords
		
		res_stack = [url_model]
		
		while len(res_stack) > 0:
			res_url = res_stack.pop()

			if res_url in self.shared:
				continue

			if res_url in self.completed:
				continue

			res_buffer = self.load_resource(res_url)
			if res_buffer is None:
				print('Unable to load resource ', res_url)
				continue

			if res_url.endswith('.xml'):
				try:
					res_xml = etree.parse(res_buffer)
				except etree.XMLSyntaxError:
					print('Retry ...', end=' ', flush=True)
					res_data = None
					res_stack.append(res_url)
					continue
				res_extrefs = self.process_resource(res_url, res_xml)
				res_data = etree.tostring(res_xml, encoding='utf-8')
				res_stack.extend(res_extrefs)
			else:
				res_data = res_buffer.read()
			
			self.add_resource(res_url, res_data)
			
		return self.resources

	def add_resource(self, res_url, res_data):
		self.resources.append((res_url, res_data))
		self.completed.add(res_url)

	def resolve(self, url):
		# test if model
		model_file = '%d.xml' % self.coords[2]
		if url.endswith(model_file):
			return model_file
		
		# test if asset
		asset_file = '%d-asset.xml' % self.coords[2]
		if url.endswith(asset_file):
			return asset_file

		h = sha1(url.encode()).hexdigest()
		# test if other tile related file
		rx = '.*/%d/%d/%d(.+)$' % self.coords
		# print(rx)
		# print(url)
		result = re.match(rx, url)
		if result is not None:
			# print('Group:', result.group(1))
			filename = '%d-%s%s' % (self.coords[2], h, result.group(1))
			return filename

		# else it must be a shared file
		basename = path.basename(url)
		name, ext = path.splitext(basename)
		filename = '%s-%s%s' % (name, h, ext)
		return '../../shared/' + filename
	
	
class BulkLoader(object):
	def __init__(self, endpoint, outdir):
		self.endpoint = endpoint
		self.outdir = path.join(getcwd(), outdir)
		self.shared = set()
		self.shared_path = path.realpath(path.join(self.outdir, 'shared'))
		self.max_wait = 0.2

	def load_single_tile(self, z, x, y):
		tile = TileLoader(z, x, y, self.shared)
		resources = tile.load(self.endpoint)

		for url, data in resources:
			res_url = tile.resolve(url)
			res_path = path.realpath(path.join(self.outdir, str(z), str(x), res_url))
			self.store_resource(res_path, data)
			if path.dirname(res_path) == self.shared_path:
				self.shared.add(url)
	
	def load_tile_range(self, zoom, min, max):
		dx = max[0] - min[0] + 1
		dy = max[1] - min[1] + 1
		c = float(dx*dy)
		for xi in range(dx):
			for yi in range(dy):
				p = 100.0 * float(xi*dy + yi) / c
				x = min[0]+xi
				y = min[1]+yi
				print('Load tile %d, %d ...' % (x, y), end = ' ', flush=True)
				self.load_single_tile(zoom, x, y)
				print('%.2f%%' % p)
				t = self.max_wait * random()
				# print("Wait for %f seconds ..." % t)
				sleep(t)

	def store_resource(self, res_path, res_data):
		res_dir = path.dirname(res_path)
		makedirs(res_dir, exist_ok = True)
		# print('Store', res_path)
		with open(res_path, 'wb') as fo:
			fo.write(res_data)
	
	

if __name__ == "__main__":
	import sys
	
	endpoint = 'http://130.206.80.175/api/3d-map-tiles/tum'
	outdir = 'output/static-tum'
	
	# campus SB
	# zoom = 18
	# bbox = (136189, 89735, 136208, 89748)
	# campus TUM
	zoom = 17
	bbox = (69783, 45415, 69788, 45419)
	# bbox = (136197, 89741, 136197, 89741)
	
	loader = BulkLoader(endpoint, outdir)
	loader.load_tile_range(zoom, bbox[0:2], bbox[2:4])
	
	
	
	